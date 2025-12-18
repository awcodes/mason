<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

it('upgrades mason brick schema from 0.x to 1.x', function () {
    // Seed database with 0.x schema data
    $oldSchema = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Hello world'],
                ],
            ],
            [
                'type' => 'masonBrick',
                'attrs' => [
                    'identifier' => 'newsletter_signup',
                    'path' => 'mason.newsletter-signup',
                    'values' => [
                        'background_color' => 'primary',
                        'heading' => 'Sign up for our newsletter.',
                    ],
                ],
            ],
            [
                'type' => 'masonBrick',
                'attrs' => [
                    'identifier' => 'section',
                    'path' => 'mason.section',
                    'values' => [
                        'background_color' => 'white',
                        'text' => 'Section text',
                    ],
                ],
            ],
        ],
    ];

    DB::table('pages')->insert([
        'title' => 'Test Page',
        'content' => json_encode($oldSchema),
    ]);

    // Run the upgrade command
    $this->artisan('mason:upgrade-bricks', [
        '--table' => 'pages',
        '--column' => 'content',
    ])
        ->expectsConfirmation("Are you sure you want to update the 'content' column in the 'pages' table? This will overwrite existing data. Please make sure you have a backup.", 'yes')
        ->assertExitCode(0);

    // Verify the data was upgraded correctly
    $updatedPage = DB::table('pages')->first();
    $updatedContent = json_decode($updatedPage->content, true);

    // Check newsletter_signup brick
    $newsletterBrick = $updatedContent['content'][1];
    expect($newsletterBrick['type'])->toBe('masonBrick');
    expect($newsletterBrick['attrs'])->not->toHaveKey('identifier');
    expect($newsletterBrick['attrs'])->not->toHaveKey('path');
    expect($newsletterBrick['attrs'])->not->toHaveKey('values');
    expect($newsletterBrick['attrs']['id'])->toBe('newsletter_signup');
    expect($newsletterBrick['attrs']['config']['background_color'])->toBe('primary');
    expect($newsletterBrick['attrs']['config']['heading'])->toBe('Sign up for our newsletter.');

    // Check section brick
    $sectionBrick = $updatedContent['content'][2];
    expect($sectionBrick['type'])->toBe('masonBrick');
    expect($sectionBrick['attrs']['id'])->toBe('section');
    expect($sectionBrick['attrs']['config']['text'])->toBe('Section text');
});

it('aborts upgrade if not confirmed', function () {
    DB::table('pages')->insert([
        'title' => 'Test Page',
        'content' => json_encode(['foo' => 'bar']),
    ]);

    $this->artisan('mason:upgrade-bricks', [
        '--table' => 'pages',
        '--column' => 'content',
    ])
        ->expectsConfirmation("Are you sure you want to update the 'content' column in the 'pages' table? This will overwrite existing data. Please make sure you have a backup.", 'no')
        ->assertExitCode(1);

    $page = DB::table('pages')->first();
    expect(json_decode($page->content, true))->toBe(['foo' => 'bar']);
});

it('handles empty tables correctly', function () {
    $this->artisan('mason:upgrade-bricks', [
        '--table' => 'pages',
        '--column' => 'content',
    ])
        ->expectsConfirmation("Are you sure you want to update the 'content' column in the 'pages' table? This will overwrite existing data. Please make sure you have a backup.", 'yes')
        ->expectsOutputToContain('No records found to upgrade.')
        ->assertExitCode(0);
});
