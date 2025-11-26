<?php
// Initialize variables
$results = null;
$error = null;
$search_query = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['phone'])) {
    $search_query = preg_replace('/[^0-9]/', '', $_POST['phone']); // Sanitize input, keep numbers only
    
    // API Endpoint
    $api_url = "https://legendxdata.site/Api/simdata.php?phone=" . $search_query;

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification if needed for this specific API
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout after 30 seconds

    // Execute cURL request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Check for errors
    if (curl_errno($ch)) {
        $error = "Connection Error: " . curl_error($ch);
    } elseif ($http_code !== 200) {
        $error = "API responded with status code: " . $http_code;
    } else {
        // Decode JSON response
        $data = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($data['success']) && $data['success'] == true) {
                $results = $data['records'];
            } else {
                $error = "No records found or invalid API response.";
            }
        } else {
            $error = "Failed to parse JSON data.";
        }
    }

    // Close cURL session
    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sim Data Search</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-10 px-4">

    <!-- Header / Search Section -->
    <div class="w-full max-w-3xl bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-8 text-center text-white">
            <h1 class="text-3xl font-bold mb-2"><i class="fas fa-search-location mr-2"></i> SIM Database Search</h1>
            <p class="text-blue-100 opacity-90">Enter a phone number to retrieve ownership details.</p>
        </div>
        
        <div class="p-8">
            <form method="POST" action="" class="space-y-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-phone text-gray-400"></i>
                    </div>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($search_query); ?>" 
                           class="block w-full pl-10 pr-3 py-4 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition text-gray-900 shadow-sm text-lg" 
                           placeholder="Enter Number (e.g., 923001234567)" required>
                </div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    <i class="fas fa-search mr-2 mt-1"></i> Search Records
                </button>
            </form>
        </div>
    </div>

    <!-- Error Message -->
    <?php if ($error): ?>
    <div class="w-full max-w-3xl bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-r shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700 font-medium"><?php echo htmlspecialchars($error); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Results Section -->
    <?php if ($results): ?>
    <div class="w-full max-w-5xl">
        <div class="flex items-center justify-between mb-4 px-2">
            <h2 class="text-2xl font-bold text-gray-800">Search Results</h2>
            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-green-400">
                <?php echo count($results); ?> Record(s) Found
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($results as $record): ?>
            <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300 overflow-hidden border border-gray-100">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-indigo-700">
                        <i class="fas fa-user-circle mr-2"></i> <?php echo htmlspecialchars($record['Name'] ?? 'N/A'); ?>
                    </h3>
                    <span class="text-xs font-mono text-gray-500 bg-gray-200 px-2 py-1 rounded">PK</span>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-mobile-alt text-gray-400 w-5"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Mobile Number</p>
                            <p class="text-base font-medium text-gray-900"><?php echo htmlspecialchars($record['Mobile'] ?? 'N/A'); ?></p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-id-card text-gray-400 w-5"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">CNIC</p>
                            <p class="text-base font-medium text-gray-900"><?php echo htmlspecialchars($record['CNIC'] ?? 'N/A'); ?></p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-map-marker-alt text-gray-400 w-5"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Address</p>
                            <p class="text-sm text-gray-700 leading-relaxed"><?php echo htmlspecialchars($record['Address'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="mt-12 text-center text-gray-500 text-sm">
        <p>&copy; <?php echo date("Y"); ?> Data Search Tool. For testing purposes only.</p>
    </div>

</body>
</html>
