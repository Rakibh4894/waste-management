@extends('website.master')

@section('title', 'Pay Bill')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <h4 class="mb-3">Pay Bill for the month of {{ $data['bill_month'] }} (Tk {{ $data['amount'] }})</h4>

        <p>Select a payment method:</p>
        <div class="d-flex gap-4 mb-4 justify-content-center">
            <button class="btn btn-light border p-4 method-btn" data-method="bkash">
                <img src="{{ asset('website/assets/images/bkash.png') }}" style="height:100px;" alt="Bkash">
            </button>
            <button class="btn btn-light border p-4 method-btn" data-method="nagad">
                <img src="{{ asset('website/assets/images/nagad.jpg') }}" style="height:100px;" alt="Nagad">
            </button>
            <button class="btn btn-light border p-4 method-btn" data-method="card">
                <img src="{{ asset('website/assets/images/card.png') }}" style="height:100px;" alt="Card">
            </button>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <form id="paymentForm" method="POST" action="{{ route('payments.processPayment') }}">
                    @csrf
                    <input type="hidden" name="payment_method" id="payment_method">
                    <input type="hidden" name="payment_id" value="{{ $data['id'] ?? '' }}">

                    {{-- Bkash / Nagad --}}
                    <div id="mobileForm" style="display:none;">
                        <div class="mb-3">
                            <label>Phone Number</label>
                            <input type="text" name="mobile" id="mobile" class="form-control">
                        </div>
                        <input type="hidden" name="otp" id="otp_input">
                        <input type="hidden" name="pin" id="pin_input">
                    </div>

                    {{-- Bank Card --}}
                    <div id="cardForm" style="display:none;">
                        <div class="mb-3">
                            <label>Card Number</label>
                            <input type="text" name="card_number" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Expiry (MM/YY)</label>
                            <input type="text" name="expiry" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>CVV</label>
                            <input type="password" name="cvv" class="form-control">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3" style="display:none;" id="submitBtn">Pay Now</button>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- OTP Verification Modal -->
<div class="modal fade" id="otpModal" tabindex="-1" role="dialog" aria-labelledby="otpModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="otpModalLabel">OTP Verification</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>An OTP has been sent to your phone. Please enter it below:</p>
                <div class="form-group">
                    <label>OTP</label>
                    <input type="text" id="otpInput" class="form-control" placeholder="Enter OTP" required>
                </div>
                <div id="otpError" class="alert alert-danger" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="verifyOtpBtn">Verify OTP</button>
            </div>
        </div>
    </div>
</div>

<!-- PIN Entry Modal -->
<div class="modal fade" id="pinModal" tabindex="-1" role="dialog" aria-labelledby="pinModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pinModalLabel">Enter PIN</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Please enter your PIN to complete the payment:</p>
                <div class="form-group">
                    <label>PIN</label>
                    <input type="password" id="pinInput" class="form-control" placeholder="Enter PIN" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitPinBtn">Pay Now</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('footer_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const methodBtns = document.querySelectorAll('.method-btn');
    const mobileForm = document.getElementById('mobileForm');
    const cardForm = document.getElementById('cardForm');
    const paymentForm = document.getElementById('paymentForm');
    const paymentMethodInput = document.getElementById('payment_method');
    const submitBtn = document.getElementById('submitBtn');
    const otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
    const pinModal = new bootstrap.Modal(document.getElementById('pinModal'));
    let currentPaymentMethod = '';

    methodBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            currentPaymentMethod = this.dataset.method;
            paymentMethodInput.value = currentPaymentMethod;

            if (currentPaymentMethod === 'bkash' || currentPaymentMethod === 'nagad') {
                mobileForm.style.display = 'block';
                cardForm.style.display = 'none';
            } else if (currentPaymentMethod === 'card') {
                mobileForm.style.display = 'none';
                cardForm.style.display = 'block';
            }

            submitBtn.style.display = 'inline-block';
        });
    });

    // Handle form submission
    paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!currentPaymentMethod) {
            toastr.error('Please select a payment method');
            return;
        }
        
        if (currentPaymentMethod === 'bkash' || currentPaymentMethod === 'nagad') {
            const mobile = document.getElementById('mobile').value.trim();
            if (!mobile) {
                toastr.error('Please enter phone number');
                return;
            }
            if (!/^\d{10,11}$/.test(mobile)) {
                toastr.error('Please enter a valid phone number');
                return;
            }
            // Show OTP modal
            otpModal.show();
        } else if (currentPaymentMethod === 'card') {
            const cardNumber = document.querySelector('[name="card_number"]').value.trim();
            const expiry = document.querySelector('[name="expiry"]').value.trim();
            const cvv = document.querySelector('[name="cvv"]').value.trim();
            
            if (!cardNumber) {
                toastr.error('Please enter card number');
                return;
            }
            if (!expiry) {
                toastr.error('Please enter expiry date');
                return;
            }
            if (!cvv) {
                toastr.error('Please enter CVV');
                return;
            }
            
            // Submit card payment directly via AJAX
            submitCardPayment();
        }
        
        return false;
    }, true);

    // Handle OTP verification
    document.getElementById('verifyOtpBtn').addEventListener('click', function() {
        const otp = document.getElementById('otpInput').value;
        const otpError = document.getElementById('otpError');

        if (!otp) {
            otpError.textContent = 'Please enter OTP';
            otpError.style.display = 'block';
            return;
        }

        // AJAX call to verify OTP
        $.ajax({
            type: 'POST',
            url: '{{ route("payments.verifyOtp") }}',
            data: {
                _token: $('input[name="_token"]').val(),
                mobile: document.getElementById('mobile').value,
                otp: otp,
                payment_method: currentPaymentMethod
            },
            success: function(response) {
                if (response.success) {
                    document.getElementById('otp_input').value = otp;
                    otpError.style.display = 'none';
                    otpModal.hide();
                    toastr.success('OTP verified successfully!');
                    // Show PIN modal
                    setTimeout(() => pinModal.show(), 500);
                } else {
                    otpError.textContent = response.message || 'OTP verification failed';
                    otpError.style.display = 'block';
                    toastr.error(response.message || 'OTP verification failed');
                }
            },
            error: function() {
                otpError.textContent = 'Error verifying OTP';
                otpError.style.display = 'block';
                toastr.error('Error verifying OTP');
            }
        });
    });

    // Handle PIN submission and final payment
    document.getElementById('submitPinBtn').addEventListener('click', function() {
        const pin = document.getElementById('pinInput').value;

        if (!pin) {
            toastr.error('Please enter PIN');
            return;
        }

        document.getElementById('pin_input').value = pin;
        pinModal.hide();

        // Submit the actual form with all data
        submitMobilePayment();
    });

    function submitMobilePayment() {
        $.ajax({
            type: 'POST',
            url: '{{ route("payments.processPayment") }}',
            data: $(paymentForm).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success('Payment successful!');
                    setTimeout(() => window.location.href = '{{ route("payments.index") }}', 1500);
                } else {
                    toastr.error(response.message || 'Payment failed');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseJSON);
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    toastr.error('Validation Error:\n' + errors);
                } else {
                    toastr.error('Error processing payment');
                }
            }
        });
    }

    function submitCardPayment() {
        $.ajax({
            type: 'POST',
            url: '{{ route("payments.processPayment") }}',
            data: $(paymentForm).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success('Payment successful!');
                    setTimeout(() => window.location.href = '{{ route("payments.index") }}', 1500);
                } else {
                    toastr.error(response.message || 'Payment failed');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseJSON);
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    toastr.error('Validation Error:\n' + errors);
                } else {
                    toastr.error('Error processing payment');
                }
            }
        });
    }
});
</script>
@endsection
