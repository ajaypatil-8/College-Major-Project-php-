<?php
require_once __DIR__ . "/../includes/header.php";
?>

<section class="explore-hero">
    <h1>My Campaigns</h1>
    <p>Track all campaigns you have created and their approval status.</p>
</section>

<section class="my-campaigns-ui">

    <!-- CAMPAIGN CARD -->
    <div class="campaign-row">

        <div class="campaign-info">
            <h3>Education Support Fund</h3>
            <p>Helping underprivileged children access education.</p>
        </div>

        <div class="campaign-meta">
            <div class="meta-item">
                <span>Goal</span>
                <strong>₹50,000</strong>
            </div>

            <div class="meta-item">
                <span>Status</span>
                <strong class="status pending">Pending</strong>
            </div>

            <div class="meta-item">
                <span>Created</span>
                <strong>12 Jun 2026</strong>
            </div>
        </div>

    </div>

    <!-- CAMPAIGN CARD -->
    <div class="campaign-row">

        <div class="campaign-info">
            <h3>Disaster Relief Help</h3>
            <p>Providing immediate relief to affected families.</p>
        </div>

        <div class="campaign-meta">
            <div class="meta-item">
                <span>Goal</span>
                <strong>₹1,00,000</strong>
            </div>

            <div class="meta-item">
                <span>Status</span>
                <strong class="status approved">Approved</strong>
            </div>

            <div class="meta-item">
                <span>Created</span>
                <strong>05 Jun 2026</strong>
            </div>
        </div>

    </div>

    <!-- CAMPAIGN CARD -->
    <div class="campaign-row">

        <div class="campaign-info">
            <h3>Medical Emergency Aid</h3>
            <p>Urgent financial support for life-saving treatment.</p>
        </div>

        <div class="campaign-meta">
            <div class="meta-item">
                <span>Goal</span>
                <strong>₹2,00,000</strong>
            </div>

            <div class="meta-item">
                <span>Status</span>
                <strong class="status rejected">Rejected</strong>
            </div>

            <div class="meta-item">
                <span>Created</span>
                <strong>20 May 2026</strong>
            </div>
        </div>

    </div>

</section>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
