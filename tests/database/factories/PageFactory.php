<?php

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
                    identifier: 'section',
                    path: 'mason::bricks.section',
                    values: [
                        'background_color' => 'white',
                        'image_position' => 'start',
                        'image_alignment' => 'top',
                        'image_flush' => false,
                        'image_rounded' => true,
                        'image_shadow' => true,
                        'text' => '<h2>This is a heading</h2><p>Just some random text for a paragraph</p>',
                        'image' => null,
                        'actions' => [],
                        'actions_alignment' => null,
                    ]
                )->asJson(),
        ];
    }
}
