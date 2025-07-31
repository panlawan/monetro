<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tailwind CSS Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Tailwind CSS Test</h1>
            
            <!-- Test Basic Classes -->
            <div class="space-y-4">
                <div class="bg-blue-500 text-white p-4 rounded">
                    Blue Background
                </div>
                
                <div class="bg-green-500 text-white p-4 rounded">
                    Green Background
                </div>
                
                <div class="bg-red-500 text-white p-4 rounded">
                    Red Background
                </div>
                
                <!-- Test Responsive -->
                <div class="bg-purple-500 text-white p-2 md:p-4 lg:p-6 rounded">
                    Responsive Padding
                </div>
                
                <!-- Test Hover & Focus -->
                <button class="btn w-full">
                    Test Button
                </button>
                
                <!-- Test Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-yellow-300 p-4 rounded text-center">Col 1</div>
                    <div class="bg-pink-300 p-4 rounded text-center">Col 2</div>
                </div>
                
                <!-- Test Flexbox -->
                <div class="flex justify-between items-center bg-gray-200 p-4 rounded">
                    <span>Flex Start</span>
                    <span class="bg-indigo-500 text-white px-3 py-1 rounded">Center</span>
                    <span>Flex End</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>