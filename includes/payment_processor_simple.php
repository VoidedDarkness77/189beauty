<?php
// Simple payment processor for demo/testing
// This simulates payment processing without real payment gateways

class SimplePaymentProcessor {
    
    public function processCreditCardPayment($payment_data, $amount) {
        // Simulate credit card validation
        $card_number = str_replace(' ', '', $payment_data['card_number']);
        $expiry = $payment_data['expiry_date'];
        $cvv = $payment_data['cvv'];
        $name_on_card = $payment_data['name_on_card'] ?? '';
        
        // Basic validation
        if (empty($card_number) || strlen($card_number) < 13) {
            return ['success' => false, 'error' => 'Invalid card number'];
        }
        
        if (!$this->validateExpiryDate($expiry)) {
            return ['success' => false, 'error' => 'Invalid or expired card'];
        }
        
        if (empty($cvv) || strlen($cvv) < 3) {
            return ['success' => false, 'error' => 'Invalid CVV'];
        }
        
        if (empty($name_on_card)) {
            return ['success' => false, 'error' => 'Please enter name on card'];
        }
        
        // Simulate payment processing - always succeeds in demo
        return [
            'success' => true,
            'transaction_id' => 'CC_' . strtoupper(uniqid()),
            'payment_method' => 'credit_card',
            'amount' => $amount,
            'card_last4' => substr($card_number, -4)
        ];
    }
    
    public function processPayPalPayment($email, $amount) {
        // Simulate PayPal payment
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email address'];
        }
        
        return [
            'success' => true,
            'transaction_id' => 'PP_' . strtoupper(uniqid()),
            'payment_method' => 'paypal',
            'amount' => $amount,
            'paypal_email' => $email
        ];
    }
    
    public function processApplePayPayment($amount) {
        // Simulate Apple Pay
        return [
            'success' => true,
            'transaction_id' => 'AP_' . strtoupper(uniqid()),
            'payment_method' => 'apple_pay',
            'amount' => $amount
        ];
    }
    
    private function validateExpiryDate($expiry) {
        if (empty($expiry) || strlen($expiry) !== 5 || strpos($expiry, '/') !== 2) {
            return false;
        }
        
        list($month, $year) = explode('/', $expiry);
        $month = (int)$month;
        $year = (int)$year;
        
        // Add 2000 to 2-digit year
        if ($year < 100) {
            $year += 2000;
        }
        
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');
        
        if ($year < $currentYear) {
            return false; // Card expired
        }
        
        if ($year === $currentYear && $month < $currentMonth) {
            return false; // Card expired this year
        }
        
        if ($month < 1 || $month > 12) {
            return false; // Invalid month
        }
        
        // Card shouldn't be expired more than 10 years in future
        if ($year > $currentYear + 10) {
            return false;
        }
        
        return true;
    }
    
    // Helper function to mask card number
    public function maskCardNumber($card_number) {
        $card_number = str_replace(' ', '', $card_number);
        if (strlen($card_number) > 4) {
            return str_repeat('*', strlen($card_number) - 4) . substr($card_number, -4);
        }
        return $card_number;
    }
}
?>