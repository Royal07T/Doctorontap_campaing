@extends('layouts.caregiver')

@section('title', 'WhatsApp Testing Sandbox')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fab fa-whatsapp me-2"></i> Send Test WhatsApp Message</h5>
                </div>
                <div class="card-body">
                    @if(config('services.vonage.messages_sandbox'))
                        <div class="alert alert-info border-0 shadow-sm mb-4">
                            <strong><i class="fas fa-flask me-2"></i> Sandbox Mode Active</strong><br>
                            Messages will be sent via the Vonage Sandbox. Ensure your recipient has joined your sandbox by sending the join keyword to your sandbox number.
                        </div>
                    @endif

                    <form id="whatsappTestForm">
                        @csrf
                        <div class="mb-3">
                            <label for="phone" class="form-label">Recipient Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="e.g. 2347081114942" required>
                            <div class="form-text">Include country code, no + sign.</div>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message Content</label>
                            <textarea class="form-control" id="message" name="message" rows="4" placeholder="Type your test message here..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100 py-2 shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i> Send Message
                        </button>
                    </form>
                    <div id="responseMessage" class="mt-3" style="display: none;"></div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-robot me-2"></i> Interactive Auto-Reply Demo</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Test the interactive bot by sending a **number** from your phone to your Vonage WhatsApp number. The bot will multiply it and reply back!</p>
                    <div class="bg-light p-3 rounded border">
                        <code class="text-success">
                            User: 5<br>
                            Bot: The answer is 15! We multiplied your number (5) by 3...
                        </code>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i> Inbound WhatsApp Logs (Recent)</h5>
                    <button class="btn btn-sm btn-outline-light" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">From</th>
                                    <th class="border-0">Message</th>
                                    <th class="border-0">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inboundMessages as $msg)
                                    @php $rawData = json_decode($msg->raw_data, true); @endphp
                                    <tr>
                                        <td><strong>{{ $msg->from }}</strong></td>
                                        <td>
                                            {{ $msg->message }}
                                            @if(isset($rawData['message']['type']) && $rawData['message']['type'] != 'text')
                                                <span class="badge bg-info text-dark ms-1">{{ $rawData['message']['type'] }}</span>
                                            @endif
                                        </td>
                                        <td><small class="text-muted">{{ \Carbon\Carbon::parse($msg->timestamp)->diffForHumans() }}</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No inbound messages found yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('whatsappTestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button');
    const responseDiv = document.getElementById('responseMessage');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sending...';
    responseDiv.style.display = 'none';

    const formData = new FormData(this);
    
    try {
        const response = await fetch('{{ route("customer-care.whatsapp.send") }}', {
            method: 'POST',
            body: JSON.stringify({
                patient_id: 1, // Placeholder for testing
                phone: formData.get('phone'),
                message: formData.get('message')
            }),
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        responseDiv.style.display = 'block';
        if (result.success) {
            responseDiv.className = 'alert alert-success border-0 shadow-sm';
            responseDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i> ' + result.message;
            this.reset();
        } else {
            responseDiv.className = 'alert alert-danger border-0 shadow-sm';
            responseDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i> ' + (result.message || 'Failed to send message');
        }
    } catch (error) {
        responseDiv.style.display = 'block';
        responseDiv.className = 'alert alert-danger border-0 shadow-sm';
        responseDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i> System error occurred.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Send Message';
    }
});
</script>
@endpush
@endsection
