<?php
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../config/db.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Campaigns - CrowdSpark</title>
    <style>
        /* Import Inter font */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #f59e0b 0%, #fb923c 100%);
            padding: 100px 20px 80px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
            margin-top: -80px;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
            animation: fadeInDown 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            letter-spacing: -1px;
        }

        .hero-section p {
            font-size: 1.25rem;
            opacity: 0.95;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s backwards;
            max-width: 600px;
            margin: 0 auto;
            font-weight: 500;
        }

        /* Filter Section */
        .filter-section {
            max-width: 1200px;
            margin: -40px auto 60px;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }

        .filter-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 28px 32px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            animation: slideUp 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.3s backwards;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            display: block;
            color: var(--text-main);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 15px;
            transition: all 0.3s ease;
            background: var(--bg-card);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            font-weight: 500;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
        }

        .filter-group input::placeholder {
            color: var(--text-light);
        }

        /* Campaigns Container */
        .campaigns-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 80px;
        }

        .campaigns-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            animation: fadeIn 0.6s ease-out 0.4s backwards;
        }

        .campaigns-header h2 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }

        .campaigns-count {
            color: var(--primary);
            font-weight: 700;
            font-size: 1rem;
            padding: 8px 16px;
            background: rgba(245, 158, 11, 0.1);
            border-radius: 999px;
            letter-spacing: 0.3px;
        }

        .campaigns-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 32px;
            animation: fadeIn 0.8s ease-out 0.5s backwards;
        }

        /* Campaign Card */
        .campaign-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            animation: cardAppear 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) backwards;
            position: relative;
        }

        .campaign-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }

        .campaign-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(245, 158, 11, 0.2);
        }

        .campaign-card:hover::after {
            opacity: 1;
        }

        .card-image {
            width: 100%;
            height: 240px;
            overflow: hidden;
            position: relative;
            background: var(--bg-soft);
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .campaign-card:hover .card-image img {
            transform: scale(1.08);
        }

        .card-category {
            position: absolute;
            top: 16px;
            left: 16px;
            background: linear-gradient(135deg, #f59e0b, #fb923c);
            color: white;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            z-index: 2;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
        }

        .card-body {
            padding: 28px;
        }

        .card-title {
            font-size: 1.35rem;
            color: var(--text-main);
            margin-bottom: 12px;
            font-weight: 800;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            letter-spacing: -0.3px;
            transition: color 0.3s ease;
        }

        .campaign-card:hover .card-title {
            color: var(--primary);
        }

        .card-description {
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 24px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.875rem;
        }

        .card-location {
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        .card-location::before {
            content: '📍';
            font-size: 1rem;
        }

        .card-date {
            color: var(--text-light);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .card-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            font-size: 0.9rem;
        }

        .stat-raised {
            color: var(--primary);
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.3px;
        }

        .stat-goal {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .progress-bar-container {
            width: 100%;
            height: 10px;
            background: var(--border-light);
            border-radius: 999px;
            overflow: hidden;
            margin-bottom: 24px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #f59e0b 0%, #fb923c 100%);
            border-radius: 999px;
            transition: width 1.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .progress-bar-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            animation: shimmer 2.5s infinite;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .view-btn {
            background: linear-gradient(135deg, #f59e0b, #fb923c);
            color: white;
            padding: 11px 22px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            position: relative;
            overflow: hidden;
            font-size: 13px;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .view-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .view-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
        }

        .view-btn:active {
            transform: translateY(0);
        }

        .percentage {
            font-weight: 700;
            color: var(--primary);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 100px 20px;
            animation: fadeIn 0.8s ease-out;
        }

        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 24px;
            opacity: 0.2;
            filter: grayscale(1);
        }

        .empty-state h3 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 1.05rem;
            font-weight: 500;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(60px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes shimmer {
            to {
                left: 100%;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 20px 60px;
            }

            .hero-section h1 {
                font-size: 2.5rem;
            }

            .hero-section p {
                font-size: 1.1rem;
            }

            .filter-card {
                grid-template-columns: 1fr;
                padding: 24px;
                gap: 20px;
            }

            .campaigns-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .campaigns-header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .campaigns-header h2 {
                font-size: 1.75rem;
            }

            .card-body {
                padding: 24px;
            }
        }

        @media (max-width: 480px) {
            .hero-section h1 {
                font-size: 2rem;
            }

            .campaigns-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Loading State */
        .loading {
            text-align: center;
            padding: 80px 20px;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 24px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <section class="hero-section">
        <h1>Explore Campaigns</h1>
        <p>Discover verified fundraisers and support causes that matter</p>
    </section>

    <!-- Filter Section -->
    <section class="filter-section">
        <div class="filter-card">
            <div class="filter-group">
                <label for="categoryFilter">Category</label>
                <select id="categoryFilter" onchange="filterCampaigns()">
                    <option value="">All Categories</option>
                    <option value="Medical">Medical</option>
                    <option value="Education">Education</option>
                    <option value="Startup">Startup</option>
                    <option value="Community">Community</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="searchFilter">Search</label>
                <input type="text" id="searchFilter" placeholder="Search campaigns..." onkeyup="filterCampaigns()">
            </div>
            <div class="filter-group">
                <label for="sortFilter">Sort By</label>
                <select id="sortFilter" onchange="filterCampaigns()">
                    <option value="newest">Newest First</option>
                    <option value="popular">Most Popular</option>
                    <option value="ending">Ending Soon</option>
                </select>
            </div>
        </div>
    </section>

    <!-- Campaigns Container -->
    <section class="campaigns-container">
        <div class="campaigns-header">
            <h2>Active Campaigns</h2>
            <span class="campaigns-count" id="campaignsCount">Loading...</span>
        </div>

        <div class="campaigns-grid" id="campaignsGrid">
            <?php
            try {
                // Get campaigns with donation totals and media
                $stmt = $pdo->prepare("
                    SELECT c.*, 
                           COALESCE(SUM(d.amount), 0) as raised_amount,
                           cm.media_url,
                           cm.media_type
                    FROM campaigns c
                    LEFT JOIN donations d ON c.id = d.campaign_id
                    LEFT JOIN campaign_media cm ON c.id = cm.campaign_id AND cm.media_type = 'thumbnail'
                    WHERE c.status = 'approved'
                    GROUP BY c.id
                    ORDER BY c.created_at DESC
                ");
                $stmt->execute();
                $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($campaigns) > 0) {
                    $count = 0;
                    foreach ($campaigns as $row) {
                        $count++;
                        $percent = 0;
                        $raisedAmount = floatval($row['raised_amount']);
                        $goalAmount = floatval($row['goal']);
                        
                        if ($goalAmount > 0) {
                            $percent = min(($raisedAmount / $goalAmount) * 100, 100);
                        }

                        // Format location
                        $location = !empty($row['location']) ? htmlspecialchars($row['location']) : 'Not specified';
                        
                        // Format date
                        $endDate = !empty($row['end_date']) ? date('M d, Y', strtotime($row['end_date'])) : '';

                        // Get thumbnail from campaign_media
                        $imagePath = 'default-campaign.jpg';
                        if (!empty($row['media_url'])) {
                            $imagePath = htmlspecialchars($row['media_url']);
                        }
                        
                        // Animation delay for stagger effect
                        $delay = ($count % 6) * 0.1;
                        
                        // Get description
                        $description = '';
                        if (!empty($row['short_desc'])) {
                            $description = htmlspecialchars(substr($row['short_desc'], 0, 120));
                        }
                        
                        $title = htmlspecialchars($row['title']);
                        $category = htmlspecialchars($row['category']);
                        
                        echo "
                        <div class='campaign-card' style='animation-delay: {$delay}s' data-category='{$category}' data-title='{$title}' data-raised='{$raisedAmount}' data-created='{$row['created_at']}'>
                            <div class='card-image'>
                                <span class='card-category'>{$category}</span>
                                <img src='/CroudSpark-X/uploads/{$imagePath}' alt='{$title}' loading='lazy' onerror=\"this.src='/CroudSpark-X/uploads/default-campaign.jpg'\">
                            </div>
                            <div class='card-body'>
                                <h3 class='card-title'>{$title}</h3>
                                <p class='card-description'>{$description}</p>
                                
                                <div class='card-meta'>
                                    <span class='card-location'>{$location}</span>
                                    " . ($endDate ? "<span class='card-date'>Ends: {$endDate}</span>" : "") . "
                                </div>
                                
                                <div class='card-stats'>
                                    <span class='stat-raised'>₹" . number_format($raisedAmount, 2) . "</span>
                                    <span class='stat-goal'>Goal: ₹" . number_format($goalAmount, 2) . "</span>
                                </div>
                                
                                <div class='progress-bar-container'>
                                    <div class='progress-bar-fill' style='width: {$percent}%'></div>
                                </div>
                                
                                <div class='card-footer'>
                                    <span class='percentage'>" . number_format($percent, 1) . "%</span>
                                    <a href='/CroudSpark-X/public/campaign-details.php?id={$row['id']}' class='view-btn'>View Campaign</a>
                                </div>
                            </div>
                        </div>
                        ";
                    }
                    
                    echo "
                    <script>
                        document.getElementById('campaignsCount').textContent = '{$count} campaigns found';
                    </script>
                    ";
                } else {
                    echo "
                    <div class='empty-state'>
                        <div class='empty-state-icon'>🔍</div>
                        <h3>No Campaigns Found</h3>
                        <p>Check back soon for new fundraising campaigns</p>
                    </div>
                    <script>
                        document.getElementById('campaignsCount').textContent = '0 campaigns found';
                    </script>
                    ";
                }
            } catch (PDOException $e) {
                echo "
                <div class='empty-state'>
                    <div class='empty-state-icon'>⚠️</div>
                    <h3>Error Loading Campaigns</h3>
                    <p>Please try again later</p>
                </div>
                <script>
                    console.error('Database error: " . addslashes($e->getMessage()) . "');
                    document.getElementById('campaignsCount').textContent = 'Error loading campaigns';
                </script>
                ";
            }
            ?>
        </div>
    </section>

    <script>
        // Filter and Search Functionality
        function filterCampaigns() {
            const category = document.getElementById('categoryFilter').value.toLowerCase();
            const search = document.getElementById('searchFilter').value.toLowerCase();
            const sort = document.getElementById('sortFilter').value;
            const cards = Array.from(document.querySelectorAll('.campaign-card'));
            
            let visibleCount = 0;

            // Filter cards
            cards.forEach(card => {
                const cardCategory = card.dataset.category.toLowerCase();
                const cardTitle = card.dataset.title.toLowerCase();
                
                const categoryMatch = !category || cardCategory === category;
                const searchMatch = !search || cardTitle.includes(search);
                
                if (categoryMatch && searchMatch) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Sort visible cards
            const visibleCards = cards.filter(card => card.style.display !== 'none');
            
            if (sort === 'popular') {
                visibleCards.sort((a, b) => {
                    return parseFloat(b.dataset.raised) - parseFloat(a.dataset.raised);
                });
            } else if (sort === 'newest') {
                visibleCards.sort((a, b) => {
                    return new Date(b.dataset.created) - new Date(a.dataset.created);
                });
            }

            // Re-append sorted cards
            const grid = document.getElementById('campaignsGrid');
            visibleCards.forEach(card => grid.appendChild(card));

            // Update count
            document.getElementById('campaignsCount').textContent = `${visibleCount} campaign${visibleCount !== 1 ? 's' : ''} found`;
            
            // Show empty state if no results
            const emptyState = document.querySelector('.empty-state');
            if (visibleCount === 0 && !emptyState) {
                grid.innerHTML = `
                    <div class='empty-state'>
                        <div class='empty-state-icon'>🔍</div>
                        <h3>No Campaigns Match Your Filters</h3>
                        <p>Try adjusting your search criteria</p>
                    </div>
                `;
            }
        }

        // Smooth scroll to top
        window.addEventListener('scroll', () => {
            const cards = document.querySelectorAll('.campaign-card');
            cards.forEach(card => {
                const rect = card.getBoundingClientRect();
                const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
                
                if (isVisible && !card.classList.contains('visible')) {
                    card.classList.add('visible');
                }
            });
        });

        // Add click animation
        document.querySelectorAll('.campaign-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.classList.contains('view-btn')) {
                    const link = this.querySelector('.view-btn');
                    if (link) window.location.href = link.href;
                }
            });
        });
    </script>

</body>
</html>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>