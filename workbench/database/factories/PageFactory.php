<?php

declare(strict_types=1);

namespace Workbench\Database\Factories;

use Awcodes\Mason\Support\Faker;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Workbench\App\Models\Page;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /** @var class-string<Page> */
    protected $model = Page::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = $this->faker->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(1, 100000),
            'content' => fn (array $attributes): array => $this->content(
                heading: $attributes['title'],
                heroText: '<p>' . $this->faker->sentence(12) . '</p>',
                cardBodies: [
                    '<p>' . $this->faker->sentence(10) . '</p>',
                    '<p>' . $this->faker->sentence(10) . '</p>',
                    '<p>' . $this->faker->sentence(10) . '</p>',
                ],
                sectionText: '<h2>' . $this->faker->sentence(3) . '</h2><p>' . $this->faker->paragraph() . '</p>',
            ),
        ];
    }

    /**
     * Fixed copy and timestamps, so the seeded Workbench pages render the same
     * on every build and documentation screenshots stay reproducible.
     */
    public function deterministic(): static
    {
        $timestamp = Carbon::parse('2025-01-01 09:00:00', 'UTC');

        return $this->state(fn (): array => [
            'content' => fn (array $attributes): array => $this->content(
                heading: $attributes['title'],
                heroText: '<p>Build pages from reusable bricks, preview every change as you make it, and render the result anywhere in your application.</p>',
                cardBodies: [
                    '<p>Stack heroes, card grids, sections and your own custom bricks into a page, then drag them into the order you want.</p>',
                    '<p>Every brick renders inside a live preview, so editors see the finished page while they build it.</p>',
                    '<p>Content is stored as structured JSON and rendered to HTML with the same brick views on the front end.</p>',
                ],
                sectionText: '<h2>Designed for editors</h2><p>Mason gives content editors a visual way to assemble pages while developers keep full control over the markup. Each brick is a Filament action with its own form, so adding a new kind of content is as simple as writing a class and a Blade view.</p>',
            ),
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    /**
     * Faker builds the same structure the editor writes, so a seeded page is
     * indistinguishable from one composed by hand. See docs/faker.md.
     *
     * @param  array{0: string, 1: string, 2: string}  $cardBodies
     * @return array<string, mixed>
     */
    protected function content(string $heading, string $heroText, array $cardBodies, string $sectionText): array
    {
        return Faker::make()
            ->brick(
                id: 'hero',
                config: [
                    'background_color' => 'primary',
                    'heading' => $heading,
                    'text' => $heroText,
                ],
            )
            ->brick(
                id: 'cardGrid',
                config: [
                    'cards' => [
                        [
                            'heading' => 'Composable',
                            'body' => $cardBodies[0],
                        ],
                        [
                            'heading' => 'Previewable',
                            'body' => $cardBodies[1],
                        ],
                        [
                            'heading' => 'Renderable',
                            'body' => $cardBodies[2],
                        ],
                    ],
                ],
            )
            ->brick(id: 'divider', config: [])
            ->brick(
                id: 'pageMeta',
                config: [
                    'heading' => 'About this page',
                    'show_url' => true,
                ],
            )
            ->brick(
                id: 'section',
                config: [
                    'background_color' => 'gray',
                    'image' => null,
                    'image_position' => 'start',
                    'image_alignment' => 'top',
                    'image_rounded' => false,
                    'image_shadow' => false,
                    'text' => $sectionText,
                ],
            )
            ->asJson();
    }
}
