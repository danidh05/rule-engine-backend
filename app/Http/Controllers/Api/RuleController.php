<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EvaluateRequest;
use App\Http\Requests\StoreRuleRequest;
use App\Http\Requests\UpdateRuleRequest;
use App\Http\Resources\RuleCollection;
use App\Http\Resources\RuleResource;
use App\Services\Interfaces\RuleServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class RuleController extends Controller
{
    public function __construct(
        protected RuleServiceInterface $ruleService
    ) {}

    /**
     * Display a listing of the rules.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['is_active', 'stackable', 'search']);
            $rules = $this->ruleService->getAllRules($filters);

            return response()->json([
                'success' => true,
                'message' => 'Rules retrieved successfully',
                'data' => new RuleCollection($rules),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve rules', [
                'error' => $e->getMessage(),
                'filters' => $filters ?? [],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve rules',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created rule.
     */
    public function store(StoreRuleRequest $request): JsonResponse
    {
        try {
            $rule = $this->ruleService->createRule($request->validated());

            Log::info('Rule created successfully', [
                'rule_id' => $rule->id,
                'rule_name' => $rule->name,
                'created_by' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rule created successfully',
                'data' => new RuleResource($rule),
            ], Response::HTTP_CREATED);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            Log::error('Failed to create rule', [
                'error' => $e->getMessage(),
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create rule',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified rule.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $rule = $this->ruleService->findRule($id);

            if (!$rule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rule not found',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rule retrieved successfully',
                'data' => new RuleResource($rule),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve rule', [
                'rule_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve rule',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified rule.
     */
    public function update(UpdateRuleRequest $request, int $id): JsonResponse
    {
        try {
            $rule = $this->ruleService->updateRule($id, $request->validated());

            Log::info('Rule updated successfully', [
                'rule_id' => $id,
                'updated_by' => $request->user()?->id,
                'updated_fields' => array_keys($request->validated()),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rule updated successfully',
                'data' => new RuleResource($rule),
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rule not found',
            ], Response::HTTP_NOT_FOUND);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            Log::error('Failed to update rule', [
                'rule_id' => $id,
                'error' => $e->getMessage(),
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update rule',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified rule.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->ruleService->deleteRule($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete rule',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            Log::info('Rule deleted successfully', ['rule_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Rule deleted successfully',
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rule not found',
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Failed to delete rule', [
                'rule_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rule',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Toggle rule active status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $rule = $this->ruleService->toggleRuleStatus($id);

            Log::info('Rule status toggled', [
                'rule_id' => $id,
                'new_status' => $rule->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rule status updated successfully',
                'data' => new RuleResource($rule),
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rule not found',
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Failed to toggle rule status', [
                'rule_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle rule status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Evaluate rules against provided line and customer data.
     */
    public function evaluate(EvaluateRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $lineData = $validated['line'];
            $customerData = $validated['customer'];

            // Add categoryId to line data if product exists and categoryId not provided
            if (!isset($lineData['categoryId']) && isset($lineData['productId'])) {
                $product = \App\Models\Product::find($lineData['productId']);
                if ($product) {
                    $lineData['categoryId'] = $product->category_id;
                }
            }

            $result = $this->ruleService->evaluateRules($lineData, $customerData);

            Log::info('Rule evaluation completed successfully', [
                'line_data' => $lineData,
                'customer_data' => $customerData,
                'applied_rules' => count($result['applied'] ?? []),
                'total_discount' => $result['totalDiscount'] ?? 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rules evaluated successfully',
                'data' => [
                    'evaluation_result' => $result,
                    'meta' => [
                        'evaluated_at' => now()->toISOString(),
                        'rules_processed' => count($result['applied'] ?? []),
                        'evaluation_options' => $validated['options'] ?? [],
                    ],
                ],
            ]);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Rule engine service unavailable', [
                'error' => $e->getMessage(),
                'line_data' => $validated['line'] ?? [],
                'customer_data' => $validated['customer'] ?? [],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Rule evaluation service temporarily unavailable',
                'error' => 'Please try again later',
            ], Response::HTTP_SERVICE_UNAVAILABLE);

        } catch (\Exception $e) {
            Log::error('Rule evaluation failed', [
                'error' => $e->getMessage(),
                'line_data' => $validated['line'] ?? [],
                'customer_data' => $validated['customer'] ?? [],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Rule evaluation failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get rule evaluation statistics and health check.
     */
    public function health(): JsonResponse
    {
        try {
            $rules = $this->ruleService->getActiveRulesForEvaluation();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'service_status' => 'healthy',
                    'active_rules_count' => $rules->count(),
                    'rule_engine_url' => config('services.rule_engine.url'),
                    'last_check' => now()->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Health check failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'data' => [
                    'service_status' => 'unhealthy',
                    'error' => $e->getMessage(),
                    'last_check' => now()->toISOString(),
                ],
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
