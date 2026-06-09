<?php

namespace App\Services;

use Anthropic\Client as AnthropicClient;
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

    public function generatePostWithClaude(string $prompt, string $category = 'general'): array
    {
        return $this->callClaude($this->buildPostMessage($prompt, $category));
    }

    public function generatePostWithOpenAI(string $prompt, string $category = 'general'): array
    {
        return $this->callOpenAI($this->buildPostMessage($prompt, $category));
    }

    // ── Products ───────────────────────────────────────────────────────────

    public function generateProductWithClaude(string $prompt): array
    {
        return $this->callClaude($this->buildProductMessage($prompt));
    }

    public function generateProductWithOpenAI(string $prompt): array
    {
        return $this->callOpenAI($this->buildProductMessage($prompt));
    }

    // ── Surveys ────────────────────────────────────────────────────────────

    public function generateSurveyWithClaude(string $prompt): array
    {
        return $this->callClaude($this->buildSurveyMessage($prompt));
    }

    public function generateSurveyWithOpenAI(string $prompt): array
    {
        return $this->callOpenAI($this->buildSurveyMessage($prompt));
    }

    // ── Private: API callers ───────────────────────────────────────────────

    private function callClaude(string $message): array
    {
        $key = config('services.anthropic.key');
        if (blank($key)) {
            throw new \RuntimeException('Anthropic API key is not configured in .env (ANTHROPIC_API_KEY).');
        }
        $client   = new AnthropicClient(apiKey: $key);
        $response = $client->messages->create(
            model: 'claude-opus-4-8',
            maxTokens: 4000,
            system: $this->systemPrompt,
            messages: [['role' => 'user', 'content' => $message]],
        );
        $text = '';
        foreach ($response->content as $block) {
            if ($block->type === 'text') { $text = $block->text; break; }
        }
        return $this->parseJson($text);
    }

    private function callOpenAI(string $message): array
    {
        $key = config('services.openai.key');
        if (blank($key)) {
            throw new \RuntimeException('OpenAI API key is not configured in .env (OPENAI_API_KEY).');
        }
        $client   = OpenAI::client($key);
        $response = $client->chat()->create([
            'model'           => 'gpt-4o',
            'messages'        => [
                ['role' => 'system', 'content' => $this->systemPrompt],
                ['role' => 'user',   'content' => $message],
            ],
            'max_tokens'      => 4000,
            'response_format' => ['type' => 'json_object'],
        ]);
        return $this->parseJson($response->choices[0]->message->content ?? '');
    }

    // ── Private: message builders ──────────────────────────────────────────

    private function buildPostMessage(string $prompt, string $category): string
    {
        return <<<MSG
Write a complete blog post based on this request: "{$prompt}"

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
  "meta_description": "SEO description 140-160 chars"
}

category hint: {$category}
MSG;
    }

    private function buildProductMessage(string $prompt): string
    {
        return <<<MSG
Write complete product listing copy for an Artisan Leather product based on this description: "{$prompt}"

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
  "meta_description": "SEO description 140-160 chars"
}
MSG;
    }

    private function buildSurveyMessage(string $prompt): string
    {
        return <<<MSG
Create a customer survey for Artisan Leather based on this request: "{$prompt}"

Return a single JSON object with exactly these keys:
{
  "title": "Survey title in English (max 80 chars)",
  "description": "Survey description in English (1-2 sentences, tells respondents what it is about and how long it takes)",
  "description_ar": "Survey description in Arabic",
  "thank_you_message": "Thank you message in English shown after submission",
  "thank_you_message_ar": "Thank you message in Arabic",
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

    private function parseJson(string $text): array
    {
        $data = json_decode(trim($text), true);
        if (json_last_error() !== JSON_ERROR_NONE || ! \is_array($data)) {
            throw new \RuntimeException('AI returned invalid JSON. Please try again.');
        }
        return $data;
    }
}
