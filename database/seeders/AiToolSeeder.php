<?php

namespace Database\Seeders;

use App\Models\AiTool;
use Illuminate\Database\Seeder;

class AiToolSeeder extends Seeder
{
    public function run(): void
    {
        $tools = [
            ['name' => 'Content Writer', 'slug' => 'content-writer', 'category' => 'writing', 'icon' => 'bi-pen-fill', 'description' => 'Generate blogs, copy, and storytelling content.', 'accent_class' => 'purple'],
            ['name' => 'Code Generator', 'slug' => 'code-generator', 'category' => 'developer', 'icon' => 'bi-code-square', 'description' => 'Build code snippets and debug issues quickly.', 'accent_class' => 'blue'],
            ['name' => 'Image Generator', 'slug' => 'image-generator', 'category' => 'creative', 'icon' => 'bi-image-fill', 'description' => 'Create visual prompts and campaign concepts.', 'accent_class' => 'pink'],
            ['name' => 'Website Builder', 'slug' => 'website-builder', 'category' => 'builder', 'icon' => 'bi-window-fullscreen', 'description' => 'Design landing pages and marketing sites faster.', 'accent_class' => 'violet'],
            ['name' => 'Email Assistant', 'slug' => 'email-assistant', 'category' => 'writing', 'icon' => 'bi-envelope-paper', 'description' => 'Draft polished customer outreach messages.', 'accent_class' => 'cyan'],
            ['name' => 'SEO Assistant', 'slug' => 'seo-assistant', 'category' => 'marketing', 'icon' => 'bi-graph-up-arrow', 'description' => 'Optimize content for rankings and targeting.', 'accent_class' => 'green'],
            ['name' => 'Document Creator', 'slug' => 'document-creator', 'category' => 'productivity', 'icon' => 'bi-file-earmark-richtext', 'description' => 'Create proposals, briefs, and internal docs.', 'accent_class' => 'amber'],
            ['name' => 'Data Analyzer', 'slug' => 'data-analyzer', 'category' => 'analytics', 'icon' => 'bi-bar-chart-line', 'description' => 'Turn raw numbers into clear insights and trends.', 'accent_class' => 'red'],
        ];

        foreach ($tools as $tool) {
            AiTool::updateOrCreate(
                ['slug' => $tool['slug']],
                $tool
            );
        }
    }
}
