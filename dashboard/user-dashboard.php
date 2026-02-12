<?php
require_once __DIR__ . "/../includes/header.php";
?>

<section class="explore-hero">
    <h1>User Dashboard</h1>
    <p>Track your donations, impact, and activity.</p>
</section>

<section class="user-dashboard">

    <!-- STATS -->
    <div class="user-stats">

        <div class="u-card total">
            <div class="u-icon">💰</div>
            <span>Total Donations</span>
            <strong>6</strong>
        </div>

        <div class="u-card contributed">
            <div class="u-icon">📦</div>
            <span>Total Amount Contributed</span>
            <strong>₹9,000</strong>
        </div>

        <div class="u-card supported">
            <div class="u-icon">❤️</div>
            <span>Campaigns Supported</span>
            <strong>3</strong>
        </div>

    </div>

    <!-- RECENT DONATIONS -->
    <div class="user-section">
        <div class="user-section-header">
            <h2>Recent Donations</h2>
            <a href="/CroudSpark-X/public/my-donations.php" class="btn-secondary">
                View All
            </a>
        </div>

        <ul class="recent-donations">
            <li>
                <div>
                    <strong>₹2,000</strong> — Emergency Medical Support
                </div>
                <span class="donation-date">12 Jun 2026</span>
            </li>

            <li>
                <div>
                    <strong>₹1,000</strong> — Education Fund
                </div>
                <span class="donation-date">02 Jun 2026</span>
            </li>

            <li>
                <div>
                    <strong>₹500</strong> — Disaster Relief
                </div>
                <span class="donation-date">28 May 2026</span>
            </li>
        </ul>
    </div>

</section>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
