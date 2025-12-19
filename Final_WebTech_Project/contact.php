<?php
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center mb-5">
            <h1 class="display-4 fw-bold">Get into Contact</h1>
            <p class="lead text-muted">Have a question? Suggestion? Found a full bin?</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4 p-md-5">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" class="form-control" placeholder="Kwame Boateng">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" placeholder="name@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <select class="form-select">
                                <option>General Inquiry</option>
                                <option>Report Full Bin</option>
                                <option>Partnership</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" rows="5" placeholder="How can we help?"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>