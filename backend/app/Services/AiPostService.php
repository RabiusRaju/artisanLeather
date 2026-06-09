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
You are a professional content writer for Artisan Leather, a premium leather goods brand based in Muscat, Oman.
You write high-quality content in both English and Arabic (Gulf Arabic, formal but approachable).
Always return valid JSON only — no markdown fences, no extra text before or after the JSON object.

The business sells: leather wallets, bags, belts, accessories, custom leather items.
Tone: sophisticated, expert, trustworthy. Audience: quality-conscious shoppers in Oman and GCC.
PROMPT;

    // ── Blog Posts ─────────────────────────────────────────────────────────

    public function generatePostWithClaude(string $prompt, string $category = 'general', array $filePaths = []): array
    {
        return $this->callClaude($this->buildPostMessage($prompt, $category), $filePaths);
    }

    public function generatePostWithOpenAI(string $prompt, string $category = 'general', array $filePaths = []): array
    {
        return $this->callOpenAI($this->buildPostMessage($prompt, $category), $filePaths);
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

    // ── Private: API callers ───────────────────────────────────────────────

    private function callClaude(string $message, array $filePaths = []): array
    {
        $key = config('services.anthropic.key');
        if (blank($key)) {
            throw new \RuntimeException('Anthropic API key is not configured in .env (ANTHROPIC_API_KEY).');
        }
        $content  = $this->buildClaudeContent($message, $filePaths);
        $client   = new AnthropicClient(apiKey: $key);
        $response = $client->messages->create(
            model: 'claude-opus-4-8',
            maxTokens: 4000,
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
        $key = config('services.openai.key');
        if (blank($key)) {
            throw new \RuntimeException('OpenAI API key is not configured in .env (OPENAI_API_KEY).');
        }
        $content  = $this->buildOpenAIContent($message, $filePaths);
        $client   = OpenAI::client($key);
        $response = $client->chat()->create([
            'model'           => 'gpt-4o',
            'messages'        => [
                ['role' => 'system', 'content' => $this->systemPrompt],
                ['role' => 'user',   'content' => $content],
            ],
            'max_tokens'      => 4000,
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

    private function buildPostMessage(string $prompt, string $category): string
    {
        $existing      = Post::latest()->limit(30)->pluck('title')->toArray();
        $noRepeat      = $this->buildExistingBlock($existing, 'blog post titles');
        $searchContext = $this->buildSearchContext($prompt);

        return <<<MSG
Write a complete blog post based on this request: "{$prompt}"
{$noRepeat}
{$searchContext}

Return a single JSON object with exactly these keys:
{
  "title": "Engaging English title (max 70 chars)",
  "excerpt": "1-2 sentence English summary (max 250 chars)",
  "content": "Full English article as HTML (use <h2>, <h3>, <p>, <ul>, <li> tags, 400-700 words)",
  "title_ar": "Arabic title translation",
  "excerpt_ar": "Arabic excerpt (1-2 sentences)",
  "content_ar": "Full Arabic article as HTML (same structure as English, 400-700 words)",
  "tags": ["tag1", "tag2", "tag3"],
  "category": "one of: care-guide, style-tips, leather-knowledge, news, general",
  "read_time": 4,
  "meta_title": "SEO title under 60 chars — Artisan Leather Oman",
  "meta_description": "SEO description 140-160 chars",
  "seo_score": 78,
  "seo_notes": "3-5 concise actionable SEO tips for this specific article. Each tip on its own line starting with a dash."
}

category hint: {$category}
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
  "seo_notes": "3-5 concise actionable SEO tips specific to this product listing. Each tip on its own line starting with a dash."
}
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

    private function buildSearchContext(string $query): string
    {
        // DB setting takes priority over .env fallback
        $flat = Setting::pluck('value', 'key')->toArray();
        $key  = $flat['seo.google_cse_key'] ?? config('services.google_cse.key');
        $cx   = $flat['seo.google_cse_id']  ?? config('services.google_cse.cx');

        if (blank($key) || blank($cx)) {
            return '';
        }

        try {
            // Cache per query for 2 hours — protects the 100 searches/day free quota
            $items = Cache::remember(
                'google_cse_' . md5($query),
                now()->addHours(2),
                function () use ($key, $cx, $query) {
                    $response = Http::timeout(8)->get('https://www.googleapis.com/customsearch/v1', [
                        'key' => $key,
                        'cx'  => $cx,
                        'q'   => $query,
                        'num' => 5,
                    ]);
                    return $response->successful() ? $response->json('items', []) : [];
                }
            );

            if (empty($items)) {
                return '';
            }

            $lines = [];
            foreach ($items as $i => $item) {
                $n       = $i + 1;
                $title   = $item['title']   ?? '';
                $snippet = $item['snippet'] ?? '';
                $lines[] = "{$n}. \"{$title}\" — {$snippet}";
            }

            $list = implode("\n", $lines);

            return "\n\nHere are the current top Google search results for this topic. Use them as competitive research — understand what already exists, then write something more comprehensive, unique, and valuable:\n{$list}\n\nYour content must be noticeably better than what currently ranks: more detailed, more useful, and more specific to the Artisan Leather brand and GCC audience.";

        } catch (\Throwable) {
            return ''; // Silently skip if search fails — never break AI generation
        }
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
