<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * @inlineMarkdown($text) renders owner-written text with inline Markdown: **bold**, *italic*,
         * links and `code`. Raw HTML is escaped and javascript: links are dropped.
         */
        Blade::directive('inlineMarkdown', fn (string $expression): string => "<?php echo trim(\\Illuminate\\Support\\Str::inlineMarkdown((string) ({$expression}), ['html_input' => 'escape', 'allow_unsafe_links' => false])); ?>");
    }
}
