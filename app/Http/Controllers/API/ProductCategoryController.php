<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $show_product = $request->input('show_product');

        $category = ProductCategory::query();

        if ($id) {
            $category = $category->find($id);

            if ($category) {
                return ResponseFormatter::success(
                    $category,
                    'Data kategori produk berhasil diambil'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Data kategori produk tidak ada',
                    404
                );
            }
        }

        if ($show_product) {
            $category->with('products');
        }

        if ($name) {
            $category->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $category->paginate($limit),
            'Data list kategori produk berhasil diambil'
        );
    }
}
