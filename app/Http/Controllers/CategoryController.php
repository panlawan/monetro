<?php
// app/Http/Controllers/CategoryController.php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        
        $query = Category::forUser($userId);
        
        if ($request->type) {
            $query->byType($request->type);
        }
        
        $categories = $query->get();
        
        return view('finance.categories.index', compact('categories'));
    }
    
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense,investment',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'description' => 'nullable|string|max:1000',
        ]);
        
        $category = Category::create([
            'name' => $request->name,
            'type' => $request->type,
            'icon' => $request->icon ?? 'circle',
            'color' => $request->color ?? '#6c757d',
            'description' => $request->description,
            'user_id' => auth()->id(),
            'is_active' => true,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'category' => $category
        ]);
    }
    
    public function createDefaultCategories(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $type = $request->type ?? 'expense';
        
        $defaultCategories = Category::getDefaultCategories($type);
        $created = 0;
        
        foreach ($defaultCategories as $categoryData) {
            $exists = Category::where('user_id', $userId)
                ->where('name', $categoryData['name'])
                ->where('type', $type)
                ->exists();
                
            if (!$exists) {
                Category::create([
                    'name' => $categoryData['name'],
                    'type' => $type,
                    'icon' => $categoryData['icon'],
                    'color' => $categoryData['color'],
                    'user_id' => $userId,
                    'is_active' => true,
                ]);
                $created++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "{$created} default categories created",
            'created' => $created
        ]);
    }
}