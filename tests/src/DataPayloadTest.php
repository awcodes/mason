<?php

declare(strict_types=1);

use Awcodes\Mason\Support\DataPayload;
use Illuminate\Support\Facades\Crypt;
use Workbench\App\Models\Page;

describe('DataPayload', function () {
    it('returns null for empty data', function () {
        expect(DataPayload::encode([]))->toBeNull();
    });

    it('round trips scalar data', function () {
        $encoded = DataPayload::encode(['locale' => 'en', 'depth' => 2]);

        expect(DataPayload::decode($encoded))->toBe(['locale' => 'en', 'depth' => 2]);
    });

    it('round trips nested arrays', function () {
        $encoded = DataPayload::encode(['meta' => ['tags' => ['a', 'b']]]);

        expect(DataPayload::decode($encoded))->toBe(['meta' => ['tags' => ['a', 'b']]]);
    });

    it('resolves an eloquent model back into the decoded data', function () {
        $page = Page::factory()->create();

        $decoded = DataPayload::decode(DataPayload::encode(['record' => $page]));

        expect($decoded['record'])->toBeInstanceOf(Page::class)
            ->and($decoded['record']->getKey())->toBe($page->getKey());
    });

    it('does not put the model contents on the wire', function () {
        $page = Page::factory()->create(['title' => 'Secret Heading']);

        $encoded = DataPayload::encode(['record' => $page]);

        expect(Crypt::decryptString($encoded))->not->toContain('Secret Heading')
            ->and(Crypt::decryptString($encoded))->toContain('__mason_model');
    });

    it('yields null for a record deleted between render and request', function () {
        $page = Page::factory()->create();
        $encoded = DataPayload::encode(['record' => $page]);
        $page->delete();

        expect(DataPayload::decode($encoded)['record'])->toBeNull();
    });

    it('ignores a payload it did not encrypt', function () {
        expect(DataPayload::decode('not-a-real-payload'))->toBe([])
            ->and(DataPayload::decode(null))->toBe([])
            ->and(DataPayload::decode(''))->toBe([]);
    });

    it('rejects a closure rather than silently encoding it', function () {
        DataPayload::encode(['callback' => fn (): string => 'nope']);
    })->throws(InvalidArgumentException::class);

    it('rejects an arbitrary object', function () {
        DataPayload::encode(['thing' => new stdClass]);
    })->throws(InvalidArgumentException::class);

    it('allows an arrayable value through', function () {
        $encoded = DataPayload::encode(['tags' => collect(['a', 'b'])]);

        expect(DataPayload::decode($encoded))->toBe(['tags' => ['a', 'b']]);
    });
});
