<?php

use App\Enums\ContentType;
use App\Models\Content;
use App\Models\Provider;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->provider = Provider::factory()->create();
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

describe('POST /api/login', function () {

    it('successfully authenticates user', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'access_token',
                    'refresh_token',
                    'token_type',
                ]
            ]);
    });

    it('fails with invalid credentials', function () {
        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong12',
        ]);

        $response->assertStatus(401);
    });

    it('validates required fields', function () {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    });
});

describe('GET /api/search', function () {

    it('requires authentication', function () {
        $response = $this->getJson('/api/search');

        $response->assertStatus(401);
    });

    it('returns paginated search results', function () {
        Content::factory()->count(25)->create([
            'provider_id' => $this->provider->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/search');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'external_id',
                        'title',
                        'type',
                        'provider',
                        'metrics',
                        'scores',
                    ]
                ],
                'links',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                ]
            ]);

        expect($response->json('meta.total'))->toBe(25);
    });

    it('filters by content type', function () {
        Content::factory()->count(5)->create([
            'provider_id' => $this->provider->id,
            'type' => ContentType::VIDEO,
        ]);

        Content::factory()->count(3)->create([
            'provider_id' => $this->provider->id,
            'type' => ContentType::ARTICLE,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/search?type=video');

        $response->assertStatus(200);
        expect($response->json('meta.total'))->toBe(5);
        expect($response->json('data.0.type'))->toBe('video');
    });

    it('searches by keyword', function () {
        Content::factory()->create([
            'provider_id' => $this->provider->id,
            'title' => 'Laravel Advanced Tutorial',
            'tags' => ['php', 'backend'],
        ]);

        Content::factory()->create([
            'provider_id' => $this->provider->id,
            'title' => 'PHP Basics Guide',
            'tags' => ['php', 'beginner'],
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/search?keyword=Laravel');

        $response->assertStatus(200);
        expect($response->json('meta.total'))->toBe(1);
        expect($response->json('data.0.title'))->toContain('Laravel');
    });

    it('sorts by different fields', function () {
        Content::factory()->create([
            'provider_id' => $this->provider->id,
            'views' => 100,
            'title' => 'Low Views'
        ]);

        Content::factory()->create([
            'provider_id' => $this->provider->id,
            'views' => 1000,
            'title' => 'High Views'
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/search?sort_by=views&sort_direction=desc');

        $response->assertStatus(200);
        expect($response->json('data.0.title'))->toBe('High Views');
    });

    it('validates per_page parameter', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/search?per_page=150');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    });

    it('validates type parameter', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/search?type=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    });

    it('includes score calculations in results', function () {
        Content::factory()->create([
            'provider_id' => $this->provider->id,
            'type' => ContentType::VIDEO,
            'views' => 10000,
            'likes' => 500,
            'published_at' => now()->subDays(3),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/search');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'scores' => [
                            'total',
                            'base_score',
                            'type_coefficient',
                            'freshness_score',
                            'engagement_score',
                        ]
                    ]
                ]
            ]);

        $scores = $response->json('data.0.scores');
        expect($scores['total'])->toBeNumeric();
        expect($scores['freshness_score'])->toBe(5); // Within 7 days
    });

    it('paginates results correctly', function () {
        Content::factory()->count(50)->create([
            'provider_id' => $this->provider->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/search?per_page=10&page=2');

        $response->assertStatus(200);
        expect($response->json('meta.current_page'))->toBe(2);
        expect($response->json('meta.per_page'))->toBe(10);
        expect($response->json('meta.total'))->toBe(50);
        expect(count($response->json('data')))->toBe(10);
    });
});

describe('GET /api/search/statistics', function () {

    it('requires authentication', function () {
        $response = $this->getJson('/api/search/statistics');

        $response->assertStatus(401);
    });

    it('returns dashboard statistics', function () {
        Content::factory()->count(5)->create([
            'provider_id' => $this->provider->id,
            'type' => ContentType::VIDEO,
            'views' => 1000,
            'likes' => 50,
        ]);

        Content::factory()->count(3)->create([
            'provider_id' => $this->provider->id,
            'type' => ContentType::ARTICLE,
            'views' => 500,
            'likes' => 25,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/search/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_contents',
                    'total_videos',
                    'total_articles',
                    'total_views',
                    'total_likes',
                    'total_comments',
                    'contents_by_provider',
                    'recent_activity',
                    'cached_at',
                ]
            ]);

        expect($response->json('data.total_contents'))->toBe(8);
        expect($response->json('data.total_videos'))->toBe(5);
        expect($response->json('data.total_articles'))->toBe(3);
        expect($response->json('data.total_views'))->toBeGreaterThan(0);
    });

    it('includes recent activity metrics', function () {
        Content::factory()->create([
            'provider_id' => $this->provider->id,
            'created_at' => now()->subHours(12),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/search/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'recent_activity' => [
                        'last_24h',
                        'last_7d',
                        'last_30d',
                    ]
                ]
            ]);

        expect($response->json('data.recent_activity.last_24h'))->toBeGreaterThanOrEqual(1);
    });
});

describe('GET /api/user', function () {

    it('returns authenticated user info', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                ]
            ]);

        expect($response->json('data.id'))->toBe($this->user->id);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    });
});

describe('GET /api/user/logout', function () {

    it('successfully logs out user', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/user/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => __('auth.logout')]);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/user/logout');

        $response->assertStatus(401);
    });
});

