<?php

declare(strict_types=1);

namespace Workbench\Database\Factories;

use Awcodes\Mason\Support\Faker;
use Illuminate\Database\Eloquent\Factories\Factory;
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
            // Faker builds the same structure the editor writes, so a seeded page
            // is indistinguishable from one composed by hand. See docs/faker.md.
            'content' => fn (array $attributes): array => Faker::make()
                ->brick(
                    id: 'hero',
                    config: [
                        'background_color' => 'primary',
                        'heading' => $attributes['title'],
                        'text' => '<p>' . $this->faker->sentence(12) . '</p>',
                    ],
                )
                ->brick(
                    id: 'cardGrid',
                    config: [
                        'cards' => [
                            [
                                'heading' => 'Composable',
                                'body' => '<p>' . $this->faker->sentence(10) . '</p>',
                            ],
                            [
                                'heading' => 'Previewable',
                                'body' => '<p>' . $this->faker->sentence(10) . '</p>',
                            ],
                            [
                                'heading' => 'Renderable',
                                'body' => '<p>' . $this->faker->sentence(10) . '</p>',
                            ],
                        ],
                    ],
                )
                ->brick(id: 'divider', config: [])
                ->brick(
                    id: 'section',
                    config: [
                        'background_color' => 'gray',
                        'image' => null,
                        'image_position' => 'start',
                        'image_alignment' => 'top',
                        'image_rounded' => false,
                        'image_shadow' => false,
                        'text' => '<h2>' . $this->faker->sentence(3) . '</h2><p>' . $this->faker->paragraph() . '</p>',
                    ],
                )
                ->asJson(),
        ];
    }
}
