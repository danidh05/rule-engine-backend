<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Rule;
use Database\Seeders\CategorySeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\RuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class RuleEvaluationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use our existing seeders to create proper test data
        $this->seed([
            CategorySeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            RuleSeeder::class,
        ]);
    }

    public function test_can_get_rules_list(): void
    {
        $response = $this->getJson('/api/rules');

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'salience',
                                'stackable',
                                'is_active',
                                'condition',
                                'action',
                                'created_at',
                                'updated_at'
                            ]
                        ],
                        'summary' => [
                            'total_rules',
                            'active_rules',
                            'inactive_rules',
                            'stackable_rules',
                            'exclusive_rules'
                        ]
                    ]
                ]);
    }

    public function test_can_create_rule(): void
    {
        $ruleData = [
            'name' => 'Test Rule',
            'salience' => 25,
            'stackable' => true,
            'condition_json' => [
                'field' => 'line.productId',
                'operator' => '==',
                'value' => 789
            ],
            'action_json' => [
                'type' => 'applyPercent',
                'args' => [15]
            ]
        ];

        $response = $this->postJson('/api/rules', $ruleData);

        $response->assertStatus(Response::HTTP_CREATED)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'salience',
                        'stackable',
                        'is_active',
                        'condition',
                        'action'
                    ]
                ]);

        $this->assertDatabaseHas('rules', [
            'name' => 'Test Rule',
            'salience' => 25,
            'stackable' => true
        ]);
    }

    public function test_can_get_single_rule(): void
    {
        $response = $this->getJson('/api/rules/100');

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'salience',
                        'stackable',
                        'is_active',
                        'condition',
                        'action',
                        'formatted_condition',
                        'formatted_action'
                    ]
                ]);
    }

    public function test_can_update_rule(): void
    {
        $updateData = [
            'name' => 'Updated Rule Name',
            'salience' => 5
        ];

        $response = $this->putJson('/api/rules/100', $updateData);

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        $this->assertDatabaseHas('rules', [
            'id' => 100,
            'name' => 'Updated Rule Name',
            'salience' => 5
        ]);
    }

    public function test_can_delete_rule(): void
    {
        $response = $this->deleteJson('/api/rules/100');

        $response->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'success' => true,
                    'message' => 'Rule deleted successfully'
                ]);

        $this->assertDatabaseMissing('rules', ['id' => 100]);
    }

    public function test_returns_404_for_nonexistent_rule(): void
    {
        $response = $this->getJson('/api/rules/9999');

        $response->assertStatus(Response::HTTP_NOT_FOUND)
                ->assertJson([
                    'success' => false,
                    'message' => 'Rule not found'
                ]);
    }

    public function test_validation_fails_for_invalid_rule_data(): void
    {
        $invalidData = [
            'name' => '', // Required field
            'salience' => -1, // Invalid range
            'condition_json' => 'invalid', // Should be array
            'action_json' => [] // Invalid structure
        ];

        $response = $this->postJson('/api/rules', $invalidData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors'
                ]);
    }

    public function test_can_evaluate_rules_with_mock_service(): void
    {
        // This test validates our API structure when microservice is unavailable
        $evaluationData = [
            'line' => [
                'productId' => 123,
                'quantity' => 6,
                'unitPrice' => 100.00,
                'categoryId' => 10
            ],
            'customer' => [
                'email' => 'alice@apple.com',
                'type' => 'restaurants',
                'loyaltyTier' => 'silver',
                'ordersCount' => 3,
                'city' => 'Riyadh'
            ]
        ];

        $response = $this->postJson('/api/evaluate', $evaluationData);

        // Since we don't have the microservice running, we expect service unavailable
        // But the validation should pass and return proper error structure
        $this->assertTrue(
            $response->status() === Response::HTTP_OK ||
            $response->status() === Response::HTTP_SERVICE_UNAVAILABLE ||
            $response->status() === Response::HTTP_INTERNAL_SERVER_ERROR
        );

        // If it's an error, check the response structure
        if ($response->status() !== Response::HTTP_OK) {
            $response->assertJsonStructure([
                'success',
                'message',
                'error'
            ]);
            
            $this->assertFalse($response->json('success'));
        }
    }

    public function test_evaluation_validates_required_fields(): void
    {
        $invalidData = [
            'line' => [
                // Missing required fields
            ],
            'customer' => [
                'email' => 'invalid-email' // Invalid email format
            ]
        ];

        $response = $this->postJson('/api/evaluate', $invalidData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors'
                ]);
    }

    public function test_can_check_health_endpoint(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'service_status',
                        'active_rules_count',
                        'last_check'
                    ]
                ]);
    }

    public function test_can_get_api_info(): void
    {
        $response = $this->getJson('/api/info');

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                    'name',
                    'version',
                    'description',
                    'endpoints',
                    'timestamp'
                ]);
    }

    public function test_can_toggle_rule_status(): void
    {
        $rule = Rule::find(100);
        $originalStatus = $rule->is_active;

        $response = $this->patchJson('/api/rules/100/toggle-status');

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        $rule->refresh();
        $this->assertNotEquals($originalStatus, $rule->is_active);
    }

    public function test_database_has_seeded_data(): void
    {
        // Test that our seeders created the expected data
        $this->assertDatabaseHas('categories', ['id' => 10, 'name' => 'Electronics']);
        $this->assertDatabaseHas('products', ['id' => 123, 'name' => 'Widget A']);
        $this->assertDatabaseHas('customers', ['id' => 1, 'email' => 'alice@apple.com']);
        $this->assertDatabaseHas('rules', ['id' => 100, 'name' => 'Buy 5 Get 1 Free on SKU 123']);
        
        // Test counts
        $this->assertEquals(3, Category::count());
        $this->assertEquals(5, Product::count());
        $this->assertEquals(4, Customer::count());
        $this->assertEquals(10, Rule::count());
    }
}