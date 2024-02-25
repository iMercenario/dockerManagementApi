<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TranslationModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use ZipArchive;

class TranslationModelController extends Controller
{
    public function index()
    {
        $models = TranslationModel::all();
        return response()->json($models);
    }

    public function show($id)
    {
        $model = TranslationModel::find($id);

        if (!$model) {
            return response()->json(['message' => 'Model not found'], 404);
        }

        return response()->json($model);
    }



    public function uploadModel(Request $request)
    {
        $request->validate([
            'model' => 'required|file|mimes:zip',
            'is_active' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $zipFile = $request->file('model');
        $zipFileName = $zipFile->getClientOriginalName();
        $uniquePrefix = time() . '_';
        $extractPath = 'models/' . $uniquePrefix . pathinfo($zipFileName, PATHINFO_FILENAME);

        // Завантаження та розархівування моделі
        $zipFile->storeAs('models', $uniquePrefix . $zipFileName);
        $zip = new ZipArchive;
        if ($zip->open(storage_path('app/models/' . $uniquePrefix . $zipFileName)) === TRUE) {
            $zip->extractTo(storage_path('app/' . $extractPath));
            $zip->close();
            Storage::delete('models/' . $uniquePrefix . $zipFileName);
        } else {
            return response()->json(['message' => 'Failed to unzip model'], 500);
        }

        // Перетворення вхідного значення 'is_active' на булеве
        $isActive = filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN);

        // Створення запису в базі
        $model = TranslationModel::create([
            'name' => $uniquePrefix . pathinfo($zipFileName, PATHINFO_FILENAME),
            'path' => $extractPath,
            'is_active' => $isActive,
            'description' => $request->input('description'),
        ]);

        // Оновлення статусу is_active інших моделей
        if ($isActive) {
            TranslationModel::where('id', '!=', $model->id)->update(['is_active' => false]);
        }

        return response()->json(['message' => 'Model uploaded successfully', 'model' => $model]);
    }

    public function deleteModel($id)
    {
        $model = TranslationModel::findOrFail($id);

        // Видалення файлів моделі зі сховища
        Storage::deleteDirectory('models/' . $model->name);

        // Видалення запису моделі з бази даних
        $model->delete();

        return response()->json(['message' => 'Model deleted successfully']);
    }

    public function updateModel(Request $request, $id)
    {
        $model = TranslationModel::findOrFail($id);

        $request->validate([
            'model' => 'nullable|file|mimes:zip',
            'is_active' => 'nullable|string',
            'description' => 'nullable|string',
        ]);


        if ($request->hasFile('model')) {
            // Видалення старих файлів моделі
            Storage::deleteDirectory('models/' . $model->name);

            // Завантаження та розархівування нової моделі
            $zipFile = $request->file('model');
            $zipFileName = $zipFile->getClientOriginalName();
            $uniquePrefix = time() . '_';
            $extractPath = 'models/' . $uniquePrefix . pathinfo($zipFileName, PATHINFO_FILENAME);

            $zipFile->storeAs('models', $uniquePrefix . $zipFileName);
            $zip = new ZipArchive;
            if ($zip->open(storage_path('app/models/' . $uniquePrefix . $zipFileName)) === TRUE) {
                $zip->extractTo(storage_path('app/' . $extractPath));
                $zip->close();
                Storage::delete('models/' . $uniquePrefix . $zipFileName);
            } else {
                return response()->json(['message' => 'Failed to unzip model'], 500);
            }

            // Оновлення шляху моделі
            $model->path = $extractPath;
            $model->name = $uniquePrefix . pathinfo($zipFileName, PATHINFO_FILENAME);
        }

        // Оновлення інших полів моделі
        $model->is_active = filter_var($request->input('is_active', $model->is_active), FILTER_VALIDATE_BOOLEAN);
        $model->description = $request->input('description', $model->description);
        $model->save();

        // Оновлення статусу is_active інших моделей
        if ($model->is_active) {
            TranslationModel::where('id', '!=', $model->id)->update(['is_active' => false]);
        }

        return response()->json(['message' => 'Model updated successfully', 'model' => $model]);
    }


}

