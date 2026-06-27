<?php

namespace App\Services;

use Anthropic\Client as AnthropicClient;
use App\Models\Post;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Survey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use OpenAI;

class AiPostService
{
    private string $systemPrompt = <<<PROMPT
You are a senior content writer, SEO specialist, and digital marketing strategist for Artisan Leather, a premium leather goods brand based in Muscat, Oman.
You write high-quality, original, search-optimized content in both English and Arabic (Gulf Arabic, formal but approachable).
Always return valid JSON only — no markdown fences, no extra text before or after the JSON object.

The business sells: leather wallets, bags, belts, accessories, custom leather items.
Tone: sophisticated, expert, trustworthy. Audience: quality-conscious shoppers in Oman and GCC.
PROMPT;

    // ── Blog Posts ─────────────────────────────────────────────────────────

    public function generatePostWithClaude(string $prompt, string $category = 'general', array $filePaths = [], ?string $referenceUrl = null): array
    {
        return $this->generateLongFormPost('claude', $prompt, $category, $filePaths, $referenceUrl);
    }

    public function generatePostWithOpenAI(string $prompt, string $category = 'general', array $filePaths = [], ?string $referenceUrl = null): array
    {
        return $this->generateLongFormPost('openai', $prompt, $category, $filePaths, $referenceUrl);
    }

    public function translatePostToArabicWithOpenAI(string $title, string $excerpt, string $content, string $socialCaption = ''): array
    {
        return $this->callOpenAI($this->buildPostTranslationMessage($title, $excerpt, $content, $socialCaption));
    }

    public function translatePostToArabicWithClaude(string $title, string $excerpt, string $content, string $socialCaption = ''): array
    {
        return $this->callClaude($this->buildPostTranslationMessage($title, $excerpt, $content, $socialCaption));
    }

    private function callAi(string $engine, string $message, array $filePaths = []): array
    {
        return $engine === 'claude'
            ? $this->callClaude($message, $filePaths)
            : $this->callOpenAI($message, $filePaths);
    }

    /**
     * Long-form posts are generated in stages instead of one giant call:
     * a model asked for 1500-1800 words across 9 sections in a single JSON
     * response reliably undershoots (it nails the section count, since that's
     * easy to self-check, but not the words-per-section). Splitting into an
     * outline call + small section-writing batches gives each call an
     * achievable target, then the finished English article is translated
     * to Arabic in one pass (translation preserves length far more
     * reliably than independent generation).
     */
    private function generateLongFormPost(string $engine, string $prompt, string $category, array $filePaths, ?string $referenceUrl): array
    {
        $outline  = $this->callAi($engine, $this->buildOutlineMessage($prompt, $category, $referenceUrl), $filePaths);
        $sections = $outline['sections'] ?? [];

        if (count($sections) < 3) {
            throw new \RuntimeException('AI returned too few sections to build a long-form article. Try again.');
        }

        $title  = $outline['title'] ?? $prompt;
        $ctaUrl = $outline['cta_url'] ?? 'https://artisanleatherom.com/collections';

        $batches       = array_chunk($sections, 2);
        $contentHtml   = '';
        $contentChunks = [];

        foreach ($batches as $i => $batch) {
            $isFirstBatch = $i === 0;
            $isFinalBatch = $i === count($batches) - 1;
            $result       = $this->callAi($engine, $this->buildSectionBatchMessage($title, $sections, $batch, $isFirstBatch, $isFinalBatch, $ctaUrl));
            $html         = $result['html'] ?? '';
            $contentHtml  .= $html;
            $contentChunks[] = $html;
        }

        $wordCount = count(preg_split('/\s+/u', trim(strip_tags($contentHtml)), -1, PREG_SPLIT_NO_EMPTY));
        $readTime  = max(1, (int) ceil($wordCount / 200));

        // Asking the model to translate ~1500+ words in one call has the same
        // "too much in one shot" failure mode as generation does — it can
        // quietly cut the translation short and close the JSON early. So
        // Arabic and Bangla are each translated chunk-by-chunk, mirroring
        // the same batches used to write the English content.
        $excerpt        = $outline['excerpt'] ?? '';
        $socialCaption  = $outline['social_caption'] ?? '';
        $metaTranslation = $this->callAi($engine, $this->buildMetaTranslationMessage($title, $excerpt, $socialCaption));

        $contentArHtml = '';
        $contentBnHtml = '';
        foreach ($contentChunks as $chunk) {
            $arResult       = $this->callAi($engine, $this->buildContentChunkTranslationMessage($chunk, 'formal but approachable Gulf Arabic'));
            $contentArHtml .= ($arResult['html'] ?? '');

            $bnResult       = $this->callAi($engine, $this->buildContentChunkTranslationMessage($chunk, 'standard Bangla (বাংলা)'));
            $contentBnHtml .= ($bnResult['html'] ?? '');
        }

        return [
            'title'             => $title,
            'excerpt'           => $excerpt,
            'content'           => $contentHtml,
            'title_ar'          => $metaTranslation['title_ar'] ?? '',
            'excerpt_ar'        => $metaTranslation['excerpt_ar'] ?? '',
            'content_ar'        => $contentArHtml,
            'title_bn'          => $metaTranslation['title_bn'] ?? '',
            'excerpt_bn'        => $metaTranslation['excerpt_bn'] ?? '',
            'content_bn'        => $contentBnHtml,
            'social_caption'    => $socialCaption,
            'social_caption_ar' => $metaTranslation['social_caption_ar'] ?? '',
            'tags'              => $outline['tags'] ?? [],
            'category'          => $outline['category'] ?? $category,
            'read_time'         => $readTime,
            'meta_title'        => $outline['meta_title'] ?? '',
            'meta_description'  => $outline['meta_description'] ?? '',
            'seo_score'         => $outline['seo_score'] ?? 0,
            'seo_notes'         => $outline['seo_notes'] ?? '',
        ];
    }

    // ── Products ───────────────────────────────────────────────────────────

    public function generateProductWithClaude(string $prompt, array $filePaths = []): array
    {
        return $this->callClaude($this->buildProductMessage($prompt), $filePaths);
    }

    public function generateProductWithOpenAI(string $prompt, array $filePaths = []): array
    {
        return $this->callOpenAI($this->buildProductMessage($prompt), $filePaths);
    }

    // ── Surveys ────────────────────────────────────────────────────────────

    public function generateSurveyWithClaude(string $prompt, array $filePaths = []): array
    {
        return $this->callClaude($this->buildSurveyMessage($prompt), $filePaths);
    }

    public function generateSurveyWithOpenAI(string $prompt, array $filePaths = []): array
    {
        return $this->callOpenAI($this->buildSurveyMessage($prompt), $filePaths);
    }

    // ── Testimonials ────────────────────────────────────────────────────────

    public function generateTestimonialWithClaude(string $prompt): array
    {
        return $this->callClaude($this->buildTestimonialMessage($prompt));
    }

    public function generateTestimonialWithOpenAI(string $prompt): array
    {
        return $this->callOpenAI($this->buildTestimonialMessage($prompt));
    }

    // ── Homepage ────────────────────────────────────────────────────────────

    public function generateHomepageWithClaude(string $theme): array
    {
        return $this->callClaude($this->buildHomepageMessage($theme));
    }

    public function generateHomepageWithOpenAI(string $theme): array
    {
        return $this->callOpenAI($this->buildHomepageMessage($theme));
    }

    // ── About Page ─────────────────────────────────────────────────────────

    public function generateAboutPageWithClaude(string $theme): array
    {
        return $this->callClaude($this->buildAboutPageMessage($theme));
    }

    public function generateAboutPageWithOpenAI(string $theme): array
    {
        return $this->callOpenAI($this->buildAboutPageMessage($theme));
    }

    // ── Private: API callers ───────────────────────────────────────────────

    private function callClaude(string $message, array $filePaths = []): array
    {
        set_time_limit(240);

        $key = config('services.anthropic.key');
        if (blank($key)) {
            throw new \RuntimeException('Anthropic API key is not configured in .env (ANTHROPIC_API_KEY).');
        }
        $content  = $this->buildClaudeContent($message, $filePaths);
        $client   = new AnthropicClient(apiKey: $key);
        $response = $client->messages->create(
            model: 'claude-opus-4-8',
            maxTokens: 16000,
            system: $this->systemPrompt,
            messages: [['role' => 'user', 'content' => $content]],
        );
        $text = '';
        foreach ($response->content as $block) {
            if ($block->type === 'text') { $text = $block->text; break; }
        }
        return $this->parseJson($text);
    }

    private function callOpenAI(string $message, array $filePaths = []): array
    {
        set_time_limit(240);

        $key = config('services.openai.key');
        if (blank($key)) {
            throw new \RuntimeException('OpenAI API key is not configured in .env (OPENAI_API_KEY).');
        }
        $content = $this->buildOpenAIContent($message, $filePaths);
        $client  = OpenAI::factory()
            ->withApiKey($key)
            ->withHttpClient(new \GuzzleHttp\Client(['timeout' => 180, 'connect_timeout' => 10]))
            ->make();
        $response = $client->chat()->create([
            'model'           => 'gpt-4o',
            'messages'        => [
                ['role' => 'system', 'content' => $this->systemPrompt],
                ['role' => 'user',   'content' => $content],
            ],
            'max_tokens'      => 16000,
            'response_format' => ['type' => 'json_object'],
        ]);
        return $this->parseJson($response->choices[0]->message->content ?? '');
    }

    // ── Private: multimodal content builders ──────────────────────────────

    private function buildClaudeContent(string $message, array $filePaths): string|array
    {
        if (empty($filePaths)) {
            return $message;
        }

        $blocks = [['type' => 'text', 'text' => $message]];

        foreach ($filePaths as $path) {
            if (!file_exists($path)) continue;

            $mime = mime_content_type($path);
            $b64  = base64_encode(file_get_contents($path));

            if (str_starts_with($mime, 'image/')) {
                $blocks[] = [
                    'type'   => 'image',
                    'source' => ['type' => 'base64', 'media_type' => $mime, 'data' => $b64],
                ];
            } elseif ($mime === 'application/pdf') {
                $blocks[] = [
                    'type'   => 'document',
                    'source' => ['type' => 'base64', 'media_type' => 'application/pdf', 'data' => $b64],
                ];
            } elseif (str_starts_with($mime, 'text/')) {
                $blocks[] = ['type' => 'text', 'text' => "\n\n--- Attached file ---\n" . file_get_contents($path) . "\n--- End of file ---"];
            }
        }

        return $blocks;
    }

    private function buildOpenAIContent(string $message, array $filePaths): string|array
    {
        if (empty($filePaths)) {
            return $message;
        }

        $blocks   = [['type' => 'text', 'text' => $message]];
        $pdfCount = 0;

        foreach ($filePaths as $path) {
            if (!file_exists($path)) continue;

            $mime = mime_content_type($path);
            $b64  = base64_encode(file_get_contents($path));

            if (str_starts_with($mime, 'image/')) {
                $blocks[] = [
                    'type'      => 'image_url',
                    'image_url' => ['url' => "data:{$mime};base64,{$b64}"],
                ];
            } elseif ($mime === 'application/pdf') {
                $pdfCount++;
            } elseif (str_starts_with($mime, 'text/')) {
                $blocks[] = ['type' => 'text', 'text' => "\n\n--- Attached file ---\n" . file_get_contents($path) . "\n--- End of file ---"];
            }
        }

        if ($pdfCount > 0) {
            $blocks[] = ['type' => 'text', 'text' => "[Note: {$pdfCount} PDF(s) were attached but OpenAI does not support PDFs in this mode. Use Claude to analyse PDF documents.]"];
        }

        return $blocks;
    }

    // ── Private: message builders ──────────────────────────────────────────

    private function buildOutlineMessage(string $prompt, string $category, ?string $referenceUrl): string
    {
        $existing       = Post::latest()->limit(30)->pluck('title')->toArray();
        $noRepeat       = $this->buildExistingBlock($existing, 'blog post titles');
        $searchContext  = $this->buildSearchContext($prompt);
        $referenceBlock = $this->buildReferenceBlock($referenceUrl);

        return <<<MSG
You are planning a long-form blog post based on this request: "{$prompt}"
{$noRepeat}
{$referenceBlock}
{$searchContext}

Plan a LinkedIn-style founder article (first-person voice, genuinely specific and useful, not generic filler) that will be written in full afterward in a separate step. Do NOT write the full article yet — only plan it.

Return a single JSON object with exactly these keys:
{
  "title": "Engaging English title (max 70 chars)",
  "excerpt": "1-2 sentence English summary (max 250 chars)",
  "sections": [
    {"heading": "Section heading text", "brief": "1-2 sentences describing exactly what this section should cover — specific to this topic, not generic"}
  ],
  "cta_url": "the single most relevant URL from this list: https://artisanleatherom.com/collections, https://artisanleatherom.com/collections/wallets, https://artisanleatherom.com/collections/bags, https://artisanleatherom.com/collections/belts, https://artisanleatherom.com/collections/accessories, https://artisanleatherom.com/contact",
  "social_caption": "A ready-to-paste LinkedIn/social caption teasing this article, 3-5 short lines. Strong hook as the first line, build curiosity, end with a soft call-to-action to read more. Do NOT include any URL, hashtags, or placeholder link — just the caption text on its own.",
  "tags": ["tag1", "tag2", "tag3"],
  "category": "one of: care-guide, style-tips, leather-knowledge, news, general",
  "meta_title": "SEO title under 60 chars — Artisan Leather Oman",
  "meta_description": "SEO description 140-160 chars",
  "seo_score": 78,
  "seo_notes": "3-5 concise actionable SEO tips for this specific article. Each tip on its own line starting with a dash."
}

The "sections" array must contain at least 9 items, ordered logically from introduction to conclusion. The LAST section must be a reflective closing that naturally leads into a call-to-action — describe that in its brief (e.g. "Reflective closing tying back to brand values, leading into an invitation to explore the collection"); do not write the actual CTA sentence here, that happens in the writing step.

category hint: {$category}
MSG;
    }

    private function buildSectionBatchMessage(string $title, array $allSections, array $batchSections, bool $isFirstBatch, bool $isFinalBatch, string $ctaUrl): string
    {
        $outlineList = '';
        foreach ($allSections as $i => $s) {
            $outlineList .= ($i + 1) . ". {$s['heading']} — {$s['brief']}\n";
        }

        $batchList = '';
        foreach ($batchSections as $s) {
            $batchList .= "- Heading: \"{$s['heading']}\"\n  Cover: {$s['brief']}\n";
        }

        $openingInstruction = $isFirstBatch
            ? "\nThis batch includes the FIRST section of the article. Open with a personal, first-person hook — e.g. \"As I continue building Artisan Leather, I've learned...\" or \"Every time I...\". Make it clear this is the founder speaking from real experience, not a brand bio."
            : '';

        $ctaInstruction = $isFinalBatch
            ? "\nThis batch includes the FINAL section of the article. End it with a short call-to-action paragraph linking to exactly this URL: {$ctaUrl}\nExample phrasing: <p><strong>Looking for [relevant hook]?</strong> <a href=\"{$ctaUrl}\">Explore our [relevant collection] →</a></p>"
            : '';

        return <<<MSG
You are writing PART of a long-form blog post titled "{$title}" for Artisan Leather.

VOICE — non-negotiable: this is a first-person LinkedIn-style founder article. Write as "I" and "we" throughout (e.g. "I've found that...", "When we select leather, we look for...") — NEVER slip into third-person brand-bio language like "Artisan Leather embodies..." or "The brand believes...". Short punchy paragraphs of 1-3 sentences. Genuinely specific and useful, not filler.

Full article outline (for context only — do NOT write these other sections, only the ones listed below):
{$outlineList}

Write ONLY these sections now, in full, as HTML:
{$batchList}
For EACH section above: start with an <h2> containing its heading, then write 280-320 words of substantive paragraph content (use <h3> sub-headings or <ul><li> bullet lists where natural). Go deep — add a concrete example, a specific detail, or a practical tip rather than restating the brief in fewer words. This is a hard minimum per section — do not stop short.
{$openingInstruction}
{$ctaInstruction}

Return a single JSON object with exactly this key:
{
  "html": "The HTML for only these sections, concatenated in order, as one string"
}
MSG;
    }

    private function buildPostTranslationMessage(string $title, string $excerpt, string $content, string $socialCaption = ''): string
    {
        $captionBlock = blank($socialCaption)
            ? ''
            : "\nSocial caption: \"{$socialCaption}\"";

        $captionKey = blank($socialCaption)
            ? ''
            : ",\n  \"social_caption_ar\": \"Arabic translation of the social caption, same tone and length\"";

        return <<<MSG
Translate the following English blog post into formal but approachable Gulf Arabic. Preserve all HTML tags and structure in the content exactly as given — only translate the text. Translate the full content in its entirety, do not summarize or shorten it.

Title: "{$title}"

Excerpt: "{$excerpt}"
{$captionBlock}

Content (HTML):
{$content}

Return a single JSON object with exactly these keys:
{
  "title_ar": "Arabic translation of the title",
  "excerpt_ar": "Arabic translation of the excerpt",
  "content_ar": "Arabic translation of the content, same HTML structure, translated in full"{$captionKey}
}
MSG;
    }

    private function buildMetaTranslationMessage(string $title, string $excerpt, string $socialCaption): string
    {
        $captionBlock = blank($socialCaption) ? '' : "\nSocial caption: \"{$socialCaption}\"";
        $captionKey   = blank($socialCaption) ? '' : ",\n  \"social_caption_ar\": \"Arabic translation of the social caption, same tone and length\"";

        return <<<MSG
Translate the following into both (1) formal but approachable Gulf Arabic and (2) standard Bangla.

Title: "{$title}"

Excerpt: "{$excerpt}"
{$captionBlock}

Return a single JSON object with exactly these keys:
{
  "title_ar": "Arabic translation of the title",
  "excerpt_ar": "Arabic translation of the excerpt",
  "title_bn": "Bangla translation of the title",
  "excerpt_bn": "Bangla translation of the excerpt"{$captionKey}
}
MSG;
    }

    private function buildContentChunkTranslationMessage(string $html, string $language = 'formal but approachable Gulf Arabic'): string
    {
        return <<<MSG
Translate this excerpt of a blog post's HTML into {$language}. Preserve all HTML tags and structure exactly as given — only translate the visible text. Translate every sentence in full, do not summarize, shorten, or skip anything — this is one part of a longer article and every part must be translated completely.

HTML excerpt:
{$html}

Return a single JSON object with exactly this key:
{
  "html": "The fully translated HTML for this excerpt, same structure, translated in full"
}
MSG;
    }

    private function buildProductMessage(string $prompt): string
    {
        $existing      = Product::latest()->limit(50)->pluck('name')->toArray();
        $noRepeat      = $this->buildExistingBlock($existing, 'product names');
        $searchContext = $this->buildSearchContext($prompt);

        return <<<MSG
Write complete product listing copy for an Artisan Leather product based on this description: "{$prompt}"
{$noRepeat}
{$searchContext}

Return a single JSON object with exactly these keys:
{
  "name": "Product name in English (concise, max 60 chars)",
  "name_ar": "Product name in Arabic",
  "tagline": "Short compelling tagline in English (max 80 chars)",
  "tagline_ar": "Tagline in Arabic",
  "description": "Full product description in English (150-250 words, highlight quality, craftsmanship, use cases)",
  "description_ar": "Full product description in Arabic (same length and quality)",
  "material": "Material name in English (e.g. Full-grain calf leather)",
  "material_ar": "Material name in Arabic",
  "origin": "Origin in English (e.g. Handcrafted in Muscat, Oman)",
  "origin_ar": "Origin in Arabic",
  "care": "Care instructions in English (3-5 bullet points as plain text, one per line)",
  "care_ar": "Care instructions in Arabic (same structure)",
  "shipping": "Shipping info in English (2-3 sentences about packaging and delivery)",
  "shipping_ar": "Shipping info in Arabic",
  "meta_title": "SEO title under 60 chars — Artisan Leather Oman",
  "meta_description": "SEO description 140-160 chars",
  "seo_score": 74,
  "seo_notes": "3-5 concise actionable SEO tips specific to this product listing. Each tip on its own line starting with a dash.",
  "details": [
    {"detail": "Specific product feature in English — be precise (e.g. 8 card slots + 2 bill compartments)", "detail_ar": "Arabic translation of the feature", "sort_order": 0},
    {"detail": "Second feature (e.g. Full-grain leather exterior, hand-stitched edges)", "detail_ar": "Arabic translation", "sort_order": 1},
    {"detail": "Third feature (e.g. Dimensions: 11 × 9 × 1.5 cm)", "detail_ar": "Arabic translation", "sort_order": 2},
    {"detail": "Fourth feature (e.g. RFID-blocking inner lining)", "detail_ar": "Arabic translation", "sort_order": 3},
    {"detail": "Fifth feature (e.g. Slim profile fits all standard pockets)", "detail_ar": "Arabic translation", "sort_order": 4}
  ],
  "colors": [
    {"name": "Color name in English (e.g. Cognac)", "name_ar": "Arabic color name (e.g. كونياك)", "hex": "#hex matching the color", "sort_order": 0},
    {"name": "Second color (e.g. Dark Brown)", "name_ar": "Arabic translation", "hex": "#hex", "sort_order": 1},
    {"name": "Third color if applicable (e.g. Black)", "name_ar": "Arabic translation", "hex": "#hex", "sort_order": 2}
  ],
  "image_alt_texts": [
    "SEO alt text for image 1 — include product name + angle (e.g. Artisan Leather Heritage Bifold Wallet Front View | Artisan Leather Oman)",
    "SEO alt text for image 2 (e.g. Heritage Bifold Wallet Open Interior Card Slots | Artisan Leather Oman)",
    "SEO alt text for image 3 (e.g. Heritage Bifold Wallet Side Profile Full-Grain Leather | Artisan Leather Oman)",
    "SEO alt text for image 4 (e.g. Heritage Bifold Wallet Cognac Brown Detail Close-Up | Artisan Leather Oman)",
    "SEO alt text for image 5 (e.g. Heritage Bifold Wallet Handcrafted Oman Lifestyle | Artisan Leather Oman)",
    "SEO alt text for image 6 (e.g. Heritage Bifold Wallet Gift Packaging | Artisan Leather Oman)"
  ],
  "image_file_names": [
    "front-exterior",
    "open-interior",
    "card-slots-detail",
    "side-profile",
    "back-exterior",
    "lifestyle"
  ]
}

Rules for details: 4–6 items, each specific and factual — dimensions, slot counts, material grades, closures, certifications. Not marketing copy.
Rules for colors: list the 2–4 most likely colors for this product type with accurate hex codes.
Rules for image_alt_texts: always 6 items, written as if describing real photos from different angles — even if images haven't been uploaded yet. Under 100 chars each.
Rules for image_file_names: always exactly 6 items. Each item is a 2–4 word kebab-case suffix describing ONLY the view/angle/content of that image — NOT the product name (e.g. "front-exterior", "open-interior", "card-slots-detail", "stitching-closeup", "back-view", "lifestyle-hand"). If reference images are attached, look at each image IN ORDER and describe what you actually see. If no images are attached, suggest the 6 most useful photography angles for this product type. Use only lowercase letters and hyphens — no spaces, no numbers.
MSG;
    }

    private function buildSurveyMessage(string $prompt): string
    {
        $existing = Survey::latest()->limit(20)->pluck('title')->toArray();
        $noRepeat = $this->buildExistingBlock($existing, 'survey titles');

        return <<<MSG
Create a customer survey for Artisan Leather based on this request: "{$prompt}"
{$noRepeat}

Return a single JSON object with exactly these keys:
{
  "title": "Survey title in English (max 80 chars)",
  "description": "Survey description in English (1-2 sentences, tells respondents what it is about and how long it takes)",
  "description_ar": "Survey description in Arabic",
  "thank_you_message": "Thank you message in English shown after submission",
  "thank_you_message_ar": "Thank you message in Arabic",
  "seo_score": 72,
  "seo_notes": "3-5 concise tips to maximise survey response rates and reach for this specific survey. Each tip on its own line starting with a dash.",
  "questions": [
    {
      "type": "one of: single_choice, multiple_choice, rating, nps, text_short, text_long, yes_no, dropdown",
      "question": "Question text in English",
      "question_ar": "Question text in Arabic",
      "description": "Optional helper text shown below the question (empty string if not needed)",
      "options": ["option1", "option2"],
      "options_ar": ["خيار1", "خيار2"],
      "is_required": true,
      "sort_order": 0
    }
  ]
}

Rules for questions array:
- Include 5-8 varied questions appropriate for a leather goods customer survey
- For rating / nps / text_short / text_long / yes_no types: set options and options_ar to []
- For single_choice / multiple_choice / dropdown types: provide 3-5 options AND their Arabic translations
- sort_order increments from 0
MSG;
    }

    private function buildTestimonialMessage(string $prompt): string
    {
        return <<<MSG
Create a realistic customer testimonial for Artisan Leather based on this request: "{$prompt}"

Return a single JSON object with exactly these keys:
{
  "quote": "Genuine English customer quote (2-4 sentences, authentic voice — not marketing copy — include a specific detail about the product or experience)",
  "quote_ar": "Faithful Arabic translation in natural Gulf Arabic",
  "author": "Customer full name (a realistic Gulf Arabic name)",
  "location": "City, Country (GCC only: Muscat, Dubai, Abu Dhabi, Riyadh, Jeddah, Kuwait City, or Doha)",
  "product": "The Artisan Leather product purchased (e.g. Heritage Bifold Wallet, Classic Leather Belt, Leather Tote Bag)",
  "rating": 5
}

Rules:
- The quote must sound like a real customer wrote it — specific details, honest enthusiasm, personal context
- Avoid generic phrases like "amazing quality" — instead describe texture, durability, or a specific moment
- Keep the GCC/Gulf context authentic
MSG;
    }

    private function buildHomepageMessage(string $theme): string
    {
        return <<<MSG
Generate homepage hero and stats copy for Artisan Leather based on this theme: "{$theme}"

Artisan Leather is a premium leather goods brand based in Muscat, Oman — wallets, bags, belts, accessories. Tone: sophisticated, expert, timeless. Target audience: quality-conscious shoppers in Oman and GCC.

Provide every text field in both English and Arabic (Gulf Arabic, natural and professional — not a literal word-for-word translation).

Return a single JSON object with exactly these keys:
{
  "hero_eyebrow": "Short location or brand positioning line (e.g. Muscat · Sultanate of Oman)",
  "hero_eyebrow_ar": "Arabic translation of hero_eyebrow",
  "hero_headline": "Headline line 1 — white text, 2-4 impactful words (e.g. Where Leather)",
  "hero_headline_ar": "Arabic translation of hero_headline",
  "hero_headline_accent": "Headline line 2 — gold italic, 2-4 words completing the thought (e.g. Becomes Legacy)",
  "hero_headline_accent_ar": "Arabic translation of hero_headline_accent",
  "hero_subtitle": "1-2 sentence brand subtitle — sophisticated and specific, max 130 chars",
  "hero_subtitle_ar": "Arabic translation of hero_subtitle",
  "hero_cta_primary": "Primary button label (2-3 words, action-oriented, e.g. Explore Collection)",
  "hero_cta_primary_ar": "Arabic translation of hero_cta_primary",
  "hero_cta_secondary": "Secondary button label (2-3 words, e.g. Our Story)",
  "hero_cta_secondary_ar": "Arabic translation of hero_cta_secondary",
  "stat_1_value": "Stat 1 value (e.g. 100%)",
  "stat_1_label": "Stat 1 label (e.g. Handcrafted)",
  "stat_1_label_ar": "Arabic translation of stat_1_label",
  "stat_2_value": "Stat 2 value (e.g. 15+)",
  "stat_2_label": "Stat 2 label (e.g. Years of Excellence)",
  "stat_2_label_ar": "Arabic translation of stat_2_label",
  "stat_3_value": "Stat 3 value (e.g. 50+)",
  "stat_3_label": "Stat 3 label (e.g. Unique Designs)",
  "stat_3_label_ar": "Arabic translation of stat_3_label",
  "stat_4_value": "Stat 4 value (e.g. GCC)",
  "stat_4_label": "Stat 4 label (e.g. Wide Delivery)",
  "stat_4_label_ar": "Arabic translation of stat_4_label"
}
MSG;
    }

    private function buildAboutPageMessage(string $theme): string
    {
        return <<<MSG
Generate complete About page content for Artisan Leather based on this theme: "{$theme}"

Artisan Leather is a premium leather goods brand based in Muscat, Oman — founded 2009. They sell handcrafted wallets, bags, belts, and accessories. Story: started as a passion project in a single workshop, grew across the GCC through quality and word of mouth. Tone: heritage, precision, authenticity.

Provide every text field in both English and Arabic (Gulf Arabic, natural and professional — not a literal word-for-word translation). For every "*_ar" key, write the Arabic version of the field with the same key name minus "_ar".

Return a single JSON object with exactly these keys (no extras):
{
  "hero_eyebrow": "Short location/era line (e.g. Muscat · Oman · Est. 2009)",
  "hero_eyebrow_ar": "Arabic translation of hero_eyebrow",
  "hero_headline": "Page headline line 1 — white text (2-4 words)",
  "hero_headline_ar": "Arabic translation of hero_headline",
  "hero_headline_accent": "Page headline line 2 — gold italic (2-4 words)",
  "hero_headline_accent_ar": "Arabic translation of hero_headline_accent",
  "hero_subtitle": "1-sentence hero subtitle (max 80 chars)",
  "hero_subtitle_ar": "Arabic translation of hero_subtitle",
  "story_headline": "Story section headline line 1 (2-4 words)",
  "story_headline_ar": "Arabic translation of story_headline",
  "story_headline_accent": "Story section headline line 2 — gold italic (2-4 words)",
  "story_headline_accent_ar": "Arabic translation of story_headline_accent",
  "story_years": "Badge text (e.g. 16+)",
  "story_p1": "Brand story paragraph 1 (60-100 words)",
  "story_p1_ar": "Arabic translation of story_p1",
  "story_p2": "Brand story paragraph 2 (60-100 words)",
  "story_p2_ar": "Arabic translation of story_p2",
  "story_p3": "Brand story paragraph 3 (60-100 words)",
  "story_p3_ar": "Arabic translation of story_p3",
  "craft_1_num": "01",
  "craft_1_title": "Craft step 1 title (3-5 words)",
  "craft_1_title_ar": "Arabic translation of craft_1_title",
  "craft_1_body": "Craft step 1 description (30-60 words)",
  "craft_1_body_ar": "Arabic translation of craft_1_body",
  "craft_2_num": "02",
  "craft_2_title": "Craft step 2 title",
  "craft_2_title_ar": "Arabic translation of craft_2_title",
  "craft_2_body": "Craft step 2 description (30-60 words)",
  "craft_2_body_ar": "Arabic translation of craft_2_body",
  "craft_3_num": "03",
  "craft_3_title": "Craft step 3 title",
  "craft_3_title_ar": "Arabic translation of craft_3_title",
  "craft_3_body": "Craft step 3 description (30-60 words)",
  "craft_3_body_ar": "Arabic translation of craft_3_body",
  "craft_4_num": "04",
  "craft_4_title": "Craft step 4 title",
  "craft_4_title_ar": "Arabic translation of craft_4_title",
  "craft_4_body": "Craft step 4 description (30-60 words)",
  "craft_4_body_ar": "Arabic translation of craft_4_body",
  "material_1_name": "Leather material 1 name (e.g. Full Grain)",
  "material_1_name_ar": "Arabic translation of material_1_name",
  "material_1_subtitle": "Material 1 subtitle (e.g. The Pinnacle of Leather)",
  "material_1_subtitle_ar": "Arabic translation of material_1_subtitle",
  "material_1_desc": "Material 1 description (30-55 words)",
  "material_1_desc_ar": "Arabic translation of material_1_desc",
  "material_2_name": "Leather material 2 name",
  "material_2_name_ar": "Arabic translation of material_2_name",
  "material_2_subtitle": "Material 2 subtitle",
  "material_2_subtitle_ar": "Arabic translation of material_2_subtitle",
  "material_2_desc": "Material 2 description (30-55 words)",
  "material_2_desc_ar": "Arabic translation of material_2_desc",
  "material_3_name": "Leather material 3 name",
  "material_3_name_ar": "Arabic translation of material_3_name",
  "material_3_subtitle": "Material 3 subtitle",
  "material_3_subtitle_ar": "Arabic translation of material_3_subtitle",
  "material_3_desc": "Material 3 description (30-55 words)",
  "material_3_desc_ar": "Arabic translation of material_3_desc",
  "value_1_number": "I",
  "value_1_title": "Value 1 title (e.g. Heritage)",
  "value_1_title_ar": "Arabic translation of value_1_title",
  "value_1_desc": "Value 1 description (20-40 words)",
  "value_1_desc_ar": "Arabic translation of value_1_desc",
  "value_2_number": "II",
  "value_2_title": "Value 2 title (e.g. Precision)",
  "value_2_title_ar": "Arabic translation of value_2_title",
  "value_2_desc": "Value 2 description (20-40 words)",
  "value_2_desc_ar": "Arabic translation of value_2_desc",
  "value_3_number": "III",
  "value_3_title": "Value 3 title (e.g. Longevity)",
  "value_3_title_ar": "Arabic translation of value_3_title",
  "value_3_desc": "Value 3 description (20-40 words)",
  "value_3_desc_ar": "Arabic translation of value_3_desc",
  "value_4_number": "IV",
  "value_4_title": "Value 4 title (e.g. Authenticity)",
  "value_4_title_ar": "Arabic translation of value_4_title",
  "value_4_desc": "Value 4 description (20-40 words)",
  "value_4_desc_ar": "Arabic translation of value_4_desc",
  "timeline_1_year": "2009",
  "timeline_1_title": "Milestone 1 title",
  "timeline_1_title_ar": "Arabic translation of timeline_1_title",
  "timeline_1_desc": "Milestone 1 description (15-30 words)",
  "timeline_1_desc_ar": "Arabic translation of timeline_1_desc",
  "timeline_2_year": "2013",
  "timeline_2_title": "Milestone 2 title",
  "timeline_2_title_ar": "Arabic translation of timeline_2_title",
  "timeline_2_desc": "Milestone 2 description (15-30 words)",
  "timeline_2_desc_ar": "Arabic translation of timeline_2_desc",
  "timeline_3_year": "2018",
  "timeline_3_title": "Milestone 3 title",
  "timeline_3_title_ar": "Arabic translation of timeline_3_title",
  "timeline_3_desc": "Milestone 3 description (15-30 words)",
  "timeline_3_desc_ar": "Arabic translation of timeline_3_desc",
  "timeline_4_year": "2023",
  "timeline_4_title": "Milestone 4 title",
  "timeline_4_title_ar": "Arabic translation of timeline_4_title",
  "timeline_4_desc": "Milestone 4 description (15-30 words)",
  "timeline_4_desc_ar": "Arabic translation of timeline_4_desc",
  "timeline_5_year": "2025",
  "timeline_5_title": "Milestone 5 title",
  "timeline_5_title_ar": "Arabic translation of timeline_5_title",
  "timeline_5_desc": "Milestone 5 description (15-30 words)",
  "timeline_5_desc_ar": "Arabic translation of timeline_5_desc",
  "cta_heading": "CTA section heading (4-8 words)",
  "cta_heading_ar": "Arabic translation of cta_heading",
  "cta_text": "CTA body text (30-60 words, motivational, not salesy)",
  "cta_text_ar": "Arabic translation of cta_text"
}
MSG;
    }

    private function buildSearchContext(string $query): string
    {
        // DB setting takes priority over .env fallback
        $flat = Setting::pluck('value', 'key')->toArray();
        $key  = $flat['seo.serper_api_key'] ?? config('services.serper.key');

        if (blank($key)) {
            return '';
        }

        $markets = [
            'om' => ['label' => 'Oman',         'location' => 'Muscat, Oman'],
            'ae' => ['label' => 'UAE',          'location' => 'Dubai, United Arab Emirates'],
            'sa' => ['label' => 'Saudi Arabia', 'location' => 'Riyadh, Saudi Arabia'],
            'qa' => ['label' => 'Qatar',        'location' => 'Doha, Qatar'],
            'kw' => ['label' => 'Kuwait',       'location' => 'Kuwait City, Kuwait'],
            'bh' => ['label' => 'Bahrain',      'location' => 'Manama, Bahrain'],
        ];

        try {
            // Cache per query for 2 hours — limits API usage
            $items = Cache::remember(
                'serper_gcc_' . md5($query),
                now()->addHours(2),
                function () use ($key, $query, $markets) {
                    $languages = ['en' => 'EN', 'ar' => 'AR'];
                    $candidates = [];
                    foreach ($markets as $gl => $market) {
                        foreach ($languages as $hl => $langLabel) {
                            $response = Http::timeout(8)
                                ->withHeaders(['X-API-KEY' => $key, 'Content-Type' => 'application/json'])
                                ->post('https://google.serper.dev/search', [
                                    'q' => $query, 'num' => 3, 'gl' => $gl, 'hl' => $hl, 'location' => $market['location'],
                                ]);
                            if (!$response->successful()) {
                                continue;
                            }
                            foreach ($response->json('organic', []) as $item) {
                                $url = $item['link'] ?? '';
                                $candidates[] = [
                                    'title'   => $item['title']   ?? '',
                                    'snippet' => $item['snippet'] ?? '',
                                    'domain'  => parse_url($url, PHP_URL_HOST) ?: $url,
                                    'market'  => $market['label'] . ' · ' . $langLabel,
                                ];
                            }
                        }
                    }

                    // Dedupe by domain so the AI sees different competitors, not the same site repeated
                    $seenDomains = [];
                    $items = [];
                    foreach ($candidates as $candidate) {
                        if (in_array($candidate['domain'], $seenDomains, true)) {
                            continue;
                        }
                        $seenDomains[] = $candidate['domain'];
                        $items[] = $candidate;
                        if (count($items) >= 12) {
                            break;
                        }
                    }
                    return $items;
                }
            );

            if (empty($items)) {
                return '';
            }

            $lines = [];
            foreach ($items as $i => $item) {
                $n       = $i + 1;
                $market  = $item['market'];
                $title   = $item['title'];
                $snippet = $item['snippet'];
                $lines[] = "{$n}. [{$market}] \"{$title}\" — {$snippet}";
            }

            $list = implode("\n", $lines);

            return "\n\nHere are the current top Google search results across GCC markets (Oman, UAE, Saudi Arabia, Qatar, Kuwait, Bahrain) for this topic. Use them as competitive research — understand what already exists, then write something more comprehensive, unique, and valuable:\n{$list}\n\nYour content must be noticeably better than what currently ranks: more detailed, more useful, and more specific to the Artisan Leather brand and GCC audience.";

        } catch (\Throwable) {
            return ''; // Silently skip if search fails — never break AI generation
        }
    }

    private function buildReferenceBlock(?string $referenceUrl): string
    {
        if (blank($referenceUrl)) {
            return '';
        }

        $referenceText = $this->fetchReferenceArticle($referenceUrl);

        return <<<MSG

REFERENCE ARTICLE — the user shared this article as a benchmark for topic depth and structure. Study what it covers and how thoroughly, then write something at least as comprehensive and well-organized. Do NOT copy or closely paraphrase any sentence from it — every word in your output must be original, in Artisan Leather's own voice.

Reference article text:
"""
{$referenceText}
"""
MSG;
    }

    private function fetchReferenceArticle(string $url): string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException("The reference URL doesn't look valid: {$url}");
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; ArtisanLeatherBot/1.0; +https://artisanleatherom.com)'])
                ->get($url);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Could not reach the reference URL: {$e->getMessage()}");
        }

        if (!$response->successful()) {
            throw new \RuntimeException("Reference URL returned HTTP {$response->status()}.");
        }

        $text = $this->extractReadableText($response->body());

        if (blank($text)) {
            throw new \RuntimeException('Could not extract readable article text from the reference URL.');
        }

        return mb_substr($text, 0, 6000);
    }

    private function extractReadableText(string $html): string
    {
        $previousErrorSetting = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        libxml_use_internal_errors($previousErrorSetting);

        $xpath = new \DOMXPath($dom);

        foreach ($xpath->query('//script|//style|//nav|//header|//footer|//aside|//form|//iframe|//svg|//noscript') as $node) {
            $node->parentNode?->removeChild($node);
        }

        $article = $xpath->query('//article')->item(0) ?? $dom->getElementsByTagName('body')->item(0);

        if (!$article) {
            return '';
        }

        return trim(preg_replace('/\s+/u', ' ', $article->textContent ?? '') ?? '');
    }

    private function buildExistingBlock(array $existing, string $label): string
    {
        if (empty($existing)) {
            return '';
        }
        $list = '- ' . implode("\n- ", $existing);
        return "\n\nIMPORTANT — The following {$label} already exist on this website. Do NOT duplicate or closely repeat any of them. Your output must be genuinely different in topic, angle, and title:\n{$list}";
    }

    private function parseJson(string $text): array
    {
        $data = json_decode(trim($text), true);
        if (json_last_error() !== JSON_ERROR_NONE || ! \is_array($data)) {
            throw new \RuntimeException('AI returned invalid JSON. Please try again.');
        }
        return $data;
    }
}
