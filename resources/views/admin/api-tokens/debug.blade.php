<!DOCTYPE html>
<html>
<head>
    <title>Debug Form</title>
</head>
<body>
    <h1>Debug Form</h1>
    <form method="POST" action="{{ url('/admin/api-tokens') }}">
        @csrf
        <input type="text" name="name" placeholder="Token name" required>
        <input type="hidden" name="permissions[]" value="read">
        <button type="submit">Submit</button>
    </form>
    
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('Form action:', this.action);
            console.log('Form method:', this.method);
            alert('Form will be submitted to: ' + this.action);
        });
    </script>
</body>
</html>