<?php

declare(strict_types=1);

use Awcodes\Mason\Support\DataPayload;
use Awcodes\Mason\Tests\Fixtures\DataBrick;
use Workbench\App\Models\Page;
use Workbench\App\Models\User;

describe('entry route render data', function () {
    // The entry route ships behind the 'auth' middleware.
    beforeEach(function () {
        $this->actingAs(new User);
    });

    $blocks = [
        ['type' => 'masonBrick', 'attrs' => ['id' => 'data-brick', 'config' => []]],
    ];

    it('renders a brick from the record carried in the payload', function () use ($blocks) {
        $page = Page::factory()->create(['title' => 'Round Tripped']);

        $response = $this->post(route('mason.entry'), [
            'blocks' => json_encode($blocks),
            'bricks' => json_encode([DataBrick::class]),
            'data' => DataPayload::encode(['record' => $page]),
        ]);

        $response->assertOk();
        expect($response->getContent())->toContain('Round Tripped');
    });

    it('renders without data when no payload is sent', function () use ($blocks) {
        $response = $this->post(route('mason.entry'), [
            'blocks' => json_encode($blocks),
            'bricks' => json_encode([DataBrick::class]),
        ]);

        $response->assertOk();
        expect($response->getContent())->toContain('no record');
    });

    it('ignores a payload the client forged', function () use ($blocks) {
        Page::factory()->create(['title' => 'Not Yours']);

        $response = $this->post(route('mason.entry'), [
            'blocks' => json_encode($blocks),
            'bricks' => json_encode([DataBrick::class]),
            'data' => base64_encode(json_encode(['record' => ['__mason_model' => Page::class, 'key' => 1]])),
        ]);

        $response->assertOk();
        expect($response->getContent())->toContain('no record')
            ->and($response->getContent())->not->toContain('Not Yours');
    });
});
