<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();
            $validatedData = $request->validated();

            Log::info('Profile Update Started', [
                'user_id' => $user->id,
                'has_file' => $request->hasFile('avatar'),
                'old_avatar' => $user->avatar,
            ]);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatarFile = $request->file('avatar');

                Log::info('Avatar File Info', [
                    'original_name' => $avatarFile->getClientOriginalName(),
                    'size' => $avatarFile->getSize(),
                    'mime_type' => $avatarFile->getMimeType(),
                    'temp_path' => $avatarFile->getPathname(),
                    'is_valid' => $avatarFile->isValid(),
                ]);

                // Validate file
                if (! $avatarFile->isValid()) {
                    Log::error('Invalid avatar file uploaded');

                    return back()->withErrors(['avatar' => 'ไฟล์ที่อัปโหลดไม่ถูกต้อง']);
                }

                // Delete old avatar if exists
                if ($user->avatar) {
                    $oldPath = str_replace('avatars/', '', $user->avatar);
                    $oldPath = str_replace('storage/', '', $oldPath);
                    $oldPath = 'avatars/'.basename($oldPath);

                    Log::info('Deleting old avatar', ['path' => $oldPath]);

                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                        Log::info('Old avatar deleted successfully');
                    }
                }

                // Generate unique filename
                $extension = $avatarFile->getClientOriginalExtension();
                $filename = 'avatar_'.$user->id.'_'.time().'.'.$extension;

                Log::info('Storing new avatar', [
                    'filename' => $filename,
                    'storage_path' => 'avatars/'.$filename,
                ]);

                // Store the file
                $stored = $avatarFile->storeAs('avatars', $filename, 'public');

                if ($stored) {
                    $validatedData['avatar'] = $stored; // This will be 'avatars/filename.ext'
                    Log::info('Avatar stored successfully', [
                        'stored_path' => $stored,
                        'full_path' => storage_path('app/public/'.$stored),
                    ]);

                    // Verify file exists
                    if (Storage::disk('public')->exists($stored)) {
                        Log::info('Avatar file verified to exist');
                    } else {
                        Log::error('Avatar file does not exist after storage');
                    }
                } else {
                    Log::error('Failed to store avatar file');

                    return back()->withErrors(['avatar' => 'ไม่สามารถบันทึกไฟล์ได้']);
                }
            }

            // Update user data
            $user->fill($validatedData);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            Log::info('Profile updated successfully', [
                'user_id' => $user->id,
                'new_avatar' => $user->avatar,
            ]);

            return Redirect::route('profile.edit')->with('status', 'profile-updated');

        } catch (\Exception $e) {
            Log::error('Profile update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'เกิดข้อผิดพลาด: '.$e->getMessage()]);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete avatar file if exists
        if ($user->avatar) {
            $avatarPath = str_replace('storage/', '', $user->avatar);
            if (Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
