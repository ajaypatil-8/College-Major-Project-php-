<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/db.php";
?>


<html lang="en">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Campaigns - CrowdSpark</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        /* ===== THEME VARIABLES ===== */
        :root {
            /* Light Theme */
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.9);
            --bg-card-hover: rgba(255, 255, 255, 0.95);
            
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-tertiary: #64748b;
            
            --border-color: rgba(15, 23, 42, 0.1);
            --border-hover: rgba(6, 182, 212, 0.3);
            
            --orb-opacity: 0.25;
            
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.12);
        }

        [data-theme="dark"] {
            /* Dark Theme */
            --bg-primary: #0f0f0f;
            --bg-secondary: #1a1a1a;
            --bg-card: rgba(20, 20, 30, 0.85);
            --bg-card-hover: rgba(30, 30, 40, 0.9);
            
            --text-primary: #ffffff;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
            
            --border-color: rgba(255, 255, 255, 0.15);
            --border-hover: rgba(6, 182, 212, 0.4);
            
            --orb-opacity: 0.25;
            
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.5);
        }

        /* Orb colors for Explore page - Cyan/Teal */
        [data-theme="dark"] {
            --orb-1: linear-gradient(45deg, #06b6d4, #22d3ee);
            --orb-2: linear-gradient(45deg, #14b8a6, #5eead4);
            --orb-3: linear-gradient(45deg, #0ea5e9, #06b6d4);
        }

        [data-theme="light"] {
            --orb-1: linear-gradient(45deg, #06b6d4, #22d3ee);
            --orb-2: linear-gradient(45deg, #14b8a6, #5eead4);
            --orb-3: linear-gradient(45deg, #0ea5e9, #06b6d4);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            overflow-x: hidden;
            position: relative;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Animated Background - Cyan/Teal theme for Projects */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            opacity: var(--orb-opacity);
            transition: opacity 0.3s ease;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            animation: float 20s infinite ease-in-out;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: var(--orb-1);
            top: -10%;
            left: -10%;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: var(--orb-2);
            bottom: -10%;
            right: -10%;
            animation-delay: 5s;
        }

        .orb-3 {
            width: 350px;
            height: 350px;
            background: var(--orb-3);
            top: 50%;
            left: 50%;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(50px, 50px) scale(1.1); }
            50% { transform: translate(-30px, 80px) scale(0.9); }
            75% { transform: translate(40px, -40px) scale(1.05); }
        }

        /* Container */
        .explore-container {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px 40px 80px;
        }

        /* Hero Section */
        .explore-hero {
            text-align: center;
            margin-bottom: 60px;
            animation: fadeInUp 0.8s ease;
        }

        .explore-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3rem, 8vw, 5.5rem);
            font-weight: 900;
            background: linear-gradient(135deg, #fff, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            padding: 20px;
            line-height: 1.1;
        }

        [data-theme="light"] .explore-hero h1 {
            background: linear-gradient(135deg, #0f172a, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .explore-hero p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Filter Section */
        .filter-section {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 32px;
            margin-bottom: 40px;
            animation: fadeInUp 0.8s ease 0.2s both;
            position: relative;
            overflow: hidden;
        }

        .filter-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #06b6d4, #14b8a6);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
        }

        .filter-group label {
            display: block;
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            font-weight: 500;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.15);
            background: var(--bg-card);
        }

        .filter-group input::placeholder {
            color: var(--text-tertiary);
        }

        /* Campaigns Header */
        .campaigns-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            animation: fadeIn 0.6s ease 0.4s both;
        }

        .campaigns-header h2 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .campaigns-count {
            color: #06b6d4;
            font-weight: 700;
            font-size: 1rem;
            padding: 8px 16px;
            background: rgba(6, 182, 212, 0.2);
            border-radius: 999px;
            border: 1px solid rgba(6, 182, 212, 0.3);
        }

        /* Campaigns Grid */
        .campaigns-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 32px;
            animation: fadeIn 0.8s ease 0.5s both;
        }

        /* Campaign Card */
        .campaign-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            animation: cardAppear 0.6s ease both;
            position: relative;
        }

        .campaign-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }

        .campaign-card:hover {
            transform: translateY(-8px);
            border-color: var(--border-hover);
        }

        .campaign-card:hover::after {
            opacity: 1;
        }

        .card-image {
            width: 100%;
            height: 240px;
            overflow: hidden;
            position: relative;
            background: var(--bg-secondary);
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .campaign-card:hover .card-image img {
            transform: scale(1.08);
        }

        .card-category {
            position: absolute;
            top: 16px;
            left: 16px;
            background: linear-gradient(135deg, #06b6d4, #14b8a6);
            color: white;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            z-index: 2;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.35);
        }

        .card-body {
            padding: 28px;
        }

        .card-title {
            font-size: 1.35rem;
            color: var(--text-primary);
            margin-bottom: 12px;
            font-weight: 800;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            transition: color 0.3s ease;
        }

        .campaign-card:hover .card-title {
            color: #06b6d4;
        }

        .card-description {
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 24px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.95rem;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.875rem;
        }

        .card-location {
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        .card-date {
            color: var(--text-tertiary);
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
            color: #06b6d4;
            font-weight: 800;
            font-size: 1.25rem;
        }

        .stat-goal {
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .progress-bar-container {
            width: 100%;
            height: 10px;
            background: var(--border-color);
            border-radius: 999px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #06b6d4, #14b8a6);
            border-radius: 999px;
            transition: width 1.2s ease;
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

        @keyframes shimmer {
            to { left: 100%; }
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .view-btn {
            background: linear-gradient(135deg, #06b6d4, #14b8a6);
            color: white;
            padding: 11px 22px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            position: relative;
            overflow: hidden;
            font-size: 13px;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.25);
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
            box-shadow: 0 8px 20px rgba(6, 182, 212, 0.4);
        }

        .percentage {
            font-weight: 700;
            color: #06b6d4;
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
            animation: fadeIn 0.8s ease;
        }

        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 24px;
            opacity: 0.2;
        }

        .empty-state h3 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 12px;
        }

        .empty-state p {
            color: var(--text-secondary);
            font-size: 1.05rem;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Animations */
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

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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

        /* Responsive */
        @media (max-width: 968px) {
            .explore-container {
                padding: 100px 20px 60px;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .campaigns-grid {
                grid-template-columns: 1fr;
            }

            .campaigns-header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .explore-hero h1 {
                font-size: 2.5rem;
            }

            .filter-section {
                padding: 24px;
            }
        }
    </style>



    <!-- Background Animation -->
    <div class="bg-animation">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="explore-container">
        
        <!-- Hero Section -->
        <div class="explore-hero">
            <h1>Explore Campaigns</h1>
            <p>Discover verified fundraisers and support causes that matter</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-grid">
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
        </div>

        <!-- Campaigns Header -->
        <div class="campaigns-header">
            <h2>Active Campaigns</h2>
            <span class="campaigns-count" id="campaignsCount">Loading...</span>
        </div>

        <!-- Campaigns Grid -->
        <div class="campaigns-grid" id="campaignsGrid">
            <?php
            try {
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

                        $location = !empty($row['location']) ? htmlspecialchars($row['location']) : 'Not specified';
                        $endDate = !empty($row['end_date']) ? date('M d, Y', strtotime($row['end_date'])) : '';

                        $imagePath = !empty($row['media_url']) 
                        ? htmlspecialchars($row['media_url']) 
                        : 'https://via.placeholder.com/400x250';

                        
                        $delay = ($count % 6) * 0.1;
                        
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
                               <img src='{$imagePath}' alt='{$title}' loading='lazy' onerror=\"this.src='uploads/default-campaign.jpg'\">
                            </div>
                            <div class='card-body'>
                                <h3 class='card-title'>{$title}</h3>
                                <p class='card-description'>{$description}</p>
                                
                                <div class='card-meta'>
                                    <span class='card-location'><i class='fas fa-map-marker-alt'></i> {$location}</span>
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
                                    <a href='/campaign-details.php?id={$row['id']}' class='view-btn'>
                                        View Campaign <i class='fas fa-arrow-right'></i>
                                    </a>
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

    </div>

    <script>
        function filterCampaigns() {
            const category = document.getElementById('categoryFilter').value.toLowerCase();
            const search = document.getElementById('searchFilter').value.toLowerCase();
            const sort = document.getElementById('sortFilter').value;
            const cards = Array.from(document.querySelectorAll('.campaign-card'));
            
            let visibleCount = 0;

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

            const grid = document.getElementById('campaignsGrid');
            visibleCards.forEach(card => grid.appendChild(card));

            document.getElementById('campaignsCount').textContent = `${visibleCount} campaign${visibleCount !== 1 ? 's' : ''} found`;
            
            if (visibleCount === 0 && !document.querySelector('.empty-state')) {
                grid.innerHTML = `
                    <div class='empty-state'>
                        <div class='empty-state-icon'>🔍</div>
                        <h3>No Campaigns Match Your Filters</h3>
                        <p>Try adjusting your search criteria</p>
                    </div>
                `;
            }
        }

        document.querySelectorAll('.campaign-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.classList.contains('view-btn')) {
                    const link = this.querySelector('.view-btn');
                    if (link) window.location.href = link.href;
                }
            });
        });
    </script>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>


