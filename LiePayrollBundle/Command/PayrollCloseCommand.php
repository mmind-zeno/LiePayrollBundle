// Improved month validation logic and error handling

public function validateMonth($month) {
    // Ensure the month is a number between 1 and 12
    if (!is_numeric($month) || $month < 1 || $month > 12) {
        throw new InvalidArgumentException('Invalid month: Must be a number between 1 and 12.');
    }
    // Additional validation logic can be added here
    return true;
}