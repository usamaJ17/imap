<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Retrieval</title>
    <!-- Add Bootstrap 5 CSS link here -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Retrieve Emails</h1>
        @if (session('message'))
            <div class="alert alert-success mb-3">
                {{ session('message') }}
            </div>
        @endif
        <form action="{{ route('store_emails') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="number_of_emails" class="form-label">Number of Emails (or enter 0 for all):</label>
                <input type="number" name="number_of_emails" id="number_of_emails" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Retrieve Emails</button>
        </form>
    </div>

    <!-- Add Bootstrap 5 JavaScript and jQuery scripts here if needed -->
</body>
</html>
