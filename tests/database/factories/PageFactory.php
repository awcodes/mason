<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Database\Factories;

use Awcodes\Mason\Support\Faker;
use Awcodes\Mason\Tests\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => Faker::make()
                ->brick(
                    id: 'section',
                    config: [
                        'background_color' => 'white',
                        'image' => null,
                        'image_position' => 'start',
                        'image_alignment' => 'top',
                        'image_flush' => false,
                        'image_rounded' => true,
                        'image_shadow' => true,
                        'text' => '<h2>This is a heading</h2><p>Just some random text for a paragraph</p>',
                    ]
                )->asJson(),
        ];
    }
}
