<style>

/* ===== PROFESSIONAL FOOTER ===== */
.footer{
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #fff;
    padding: 80px 20px 0;
    margin-top: 100px;
    position: relative;
    overflow: hidden;
}

/* Animated background gradient */
.footer::before{
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #f59e0b, #fb923c, #fbbf24, #f59e0b);
    background-size: 200% auto;
    animation: gradientMove 3s linear infinite;
}

@keyframes gradientMove {
    0% { background-position: 0% center; }
    100% { background-position: 200% center; }
}

/* Decorative circles */
.footer::after{
    content: "";
    position: absolute;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.1), transparent 70%);
    border-radius: 50%;
    top: -200px;
    right: -200px;
    pointer-events: none;
}

.footer-container{
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

/* ===== TOP SECTION ===== */
.footer-top{
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 60px;
    padding-bottom: 60px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

/* Brand column */
.footer-brand{
    animation: fadeInUp 0.6s ease;
}

.footer-logo{
    font-size: 28px;
    font-weight: 900;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.footer-logo-icon{
    font-size: 32px;
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: pulse 2s ease infinite;
}

.footer-tagline{
    color: #cbd5e1;
    font-size: 15px;
    line-height: 1.7;
    margin-bottom: 24px;
}

.footer-social{
    display: flex;
    gap: 12px;
}

.social-link{
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    text-decoration: none;
    font-size: 18px;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.social-link:hover{
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    transform: translateY(-4px) scale(1.1);
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
    border-color: transparent;
}

/* Footer columns */
.footer-col{
    animation: fadeInUp 0.6s ease;
}

.footer-col:nth-child(2){ animation-delay: 0.1s; }
.footer-col:nth-child(3){ animation-delay: 0.2s; }
.footer-col:nth-child(4){ animation-delay: 0.3s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.footer-title{
    font-size: 16px;
    font-weight: 800;
    margin-bottom: 20px;
    color: #fff;
    position: relative;
    padding-bottom: 12px;
}

.footer-title::after{
    content: "";
    position: absolute;
    left: 0;
    bottom: 0;
    width: 40px;
    height: 3px;
    background: linear-gradient(90deg, #f59e0b, #fb923c);
    border-radius: 2px;
}

.footer-links{
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.footer-links a{
    color: #cbd5e1;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    position: relative;
}

.footer-links a i{
    font-size: 12px;
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s ease;
}

.footer-links a:hover{
    color: #f59e0b;
    padding-left: 8px;
}

.footer-links a:hover i{
    opacity: 1;
    transform: translateX(0);
}

/* ===== NEWSLETTER ===== */
.newsletter-form{
    margin-top: 20px;
    display: flex;
    gap: 8px;
}

.newsletter-input{
    flex: 1;
    padding: 12px 16px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    color: #fff;
    font-size: 14px;
    transition: all 0.3s ease;
}

.newsletter-input::placeholder{
    color: #94a3b8;
}

.newsletter-input:focus{
    outline: none;
    border-color: #f59e0b;
    background: rgba(255, 255, 255, 0.1);
}

.newsletter-btn{
    padding: 12px 24px;
    border-radius: 999px;
    border: none;
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.newsletter-btn:hover{
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
}

/* ===== BOTTOM SECTION ===== */
.footer-bottom{
    padding: 28px 0;
    margin-top: 60px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.footer-copyright{
    color: #94a3b8;
    font-size: 14px;
}

.footer-copyright a{
    color: #f59e0b;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.footer-copyright a:hover{
    color: #fb923c;
}

.footer-bottom-links{
    display: flex;
    gap: 24px;
    list-style: none;
}

.footer-bottom-links a{
    color: #94a3b8;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover{
    color: #f59e0b;
}

/* ===== BACK TO TOP BUTTON ===== */
.back-to-top{
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f59e0b, #fb923c);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
    opacity: 0;
    pointer-events: none;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 900;
}

.back-to-top.visible{
    opacity: 1;
    pointer-events: auto;
}

.back-to-top:hover{
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 12px 30px rgba(245, 158, 11, 0.5);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 968px) {
    .footer-top{
        grid-template-columns: repeat(2, 1fr);
        gap: 40px;
    }
    
    .footer-brand{
        grid-column: 1 / -1;
    }
}

@media (max-width: 640px) {
    .footer{
        padding: 60px 20px 0;
    }
    
    .footer-top{
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .footer-bottom{
        flex-direction: column;
        text-align: center;
    }
    
    .newsletter-form{
        flex-direction: column;
    }
    
    .back-to-top{
        bottom: 20px;
        right: 20px;
        width: 45px;
        height: 45px;
    }
}

/* ===== TRUST BADGES ===== */
.footer-trust{
    padding: 40px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 40px;
}

.trust-badges{
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 40px;
    flex-wrap: wrap;
}

.trust-badge{
    display: flex;
    align-items: center;
    gap: 10px;
    color: #94a3b8;
    font-size: 14px;
}

.trust-badge i{
    font-size: 24px;
    color: #f59e0b;
}

</style>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-container">
        
        <!-- Top Section -->
        <div class="footer-top">
            
            <!-- Brand Column -->
            <div class="footer-brand">
                <div class="footer-logo">
                    <span class="footer-logo-icon">✨</span>
                    CrowdSpark
                </div>
                <p class="footer-tagline">
                    Empowering dreams through community-driven crowdfunding. 
                    Join thousands of supporters making real impact across India.
                </p>
                
                <!-- Social Links -->
                <div class="footer-social">
                    <a href="#" class="social-link" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="footer-col">
                <h4 class="footer-title">Quick Links</h4>
                <ul class="footer-links">
                    <li>
                        <a href="/CroudSpark-X/public/index.php">
                            <i class="fa-solid fa-chevron-right"></i> Home
                        </a>
                    </li>
                    <li>
                        <a href="/CroudSpark-X/public/explore-campaigns.php">
                            <i class="fa-solid fa-chevron-right"></i> Browse Campaigns
                        </a>
                    </li>
                    <li>
                        <a href="/CroudSpark-X/public/about.php">
                            <i class="fa-solid fa-chevron-right"></i> About Us
                        </a>
                    </li>
                    <li>
                        <a href="/CroudSpark-X/public/how-it-works.php">
                            <i class="fa-solid fa-chevron-right"></i> How It Works
                        </a>
                    </li>
                    <li>
                        <a href="/CroudSpark-X/user/login.php">
                            <i class="fa-solid fa-chevron-right"></i> Start Campaign
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Support -->
            <div class="footer-col">
                <h4 class="footer-title">Support</h4>
                <ul class="footer-links">
                    <li>
                        <a href="/CroudSpark-X/public/faq.php">
                            <i class="fa-solid fa-chevron-right"></i> FAQ
                        </a>
                    </li>
                    <li>
                        <a href="/CroudSpark-X/public/contact.php">
                            <i class="fa-solid fa-chevron-right"></i> Contact Us
                        </a>
                    </li>
                    <li>
                        <a href="/CroudSpark-X/public/help-center.php">
                            <i class="fa-solid fa-chevron-right"></i> Help Center
                        </a>
                    </li>
                    <li>
                        <a href="/CroudSpark-X/public/trust-safety.php">
                            <i class="fa-solid fa-chevron-right"></i> Trust & Safety
                        </a>
                    </li>
                    <li>
                        <a href="/CroudSpark-X/public/report-issue.php">
                            <i class="fa-solid fa-chevron-right"></i> Report Issue
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Legal & Newsletter -->
            <div class="footer-col">
                <h4 class="footer-title">Stay Updated</h4>
                <ul class="footer-links">
                    <li>
                        <a href="/CroudSpark-X/public/privacy-policy.php">
                            <i class="fa-solid fa-chevron-right"></i> Privacy Policy
                        </a>
                    </li>
                    <li>
                        <a href="/CroudSpark-X/public/terms-of-service.php">
                            <i class="fa-solid fa-chevron-right"></i> Terms of Service
                        </a>
                    </li>
                    <li>
                        <a href="/CroudSpark-X/public/cookie-policy.php">
                            <i class="fa-solid fa-chevron-right"></i> Cookie Policy
                        </a>
                    </li>
                </ul>
                
                <!-- Newsletter -->
                <form class="newsletter-form" onsubmit="return subscribeNewsletter(event)">
                    <input 
                        type="email" 
                        class="newsletter-input" 
                        placeholder="Your email"
                        required
                    >
                    <button type="submit" class="newsletter-btn">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
            
        </div>
        
        <!-- Trust Badges -->
        <div class="footer-trust">
            <div class="trust-badges">
                <div class="trust-badge">
                    <i class="fa-solid fa-shield-check"></i>
                    <span>Secure Platform</span>
                </div>
                <div class="trust-badge">
                    <i class="fa-solid fa-lock"></i>
                    <span>SSL Encrypted</span>
                </div>
                <div class="trust-badge">
                    <i class="fa-solid fa-certificate"></i>
                    <span>Verified Campaigns</span>
                </div>
                <div class="trust-badge">
                    <i class="fa-solid fa-headset"></i>
                    <span>24/7 Support</span>
                </div>
            </div>
        </div>
        
        <!-- Bottom Section -->
        <div class="footer-bottom">
            <div class="footer-copyright">
                © 2024 <a href="/CroudSpark-X/public/index.php">CrowdSpark</a>. All rights reserved.
                Made with <span style="color: #ef4444;">❤</span> in India
            </div>
            
            <ul class="footer-bottom-links">
                <li><a href="/CroudSpark-X/public/sitemap.php">Sitemap</a></li>
                <li><a href="/CroudSpark-X/public/accessibility.php">Accessibility</a></li>
                <li><a href="/CroudSpark-X/public/press.php">Press Kit</a></li>
            </ul>
        </div>
        
    </div>
</footer>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop" onclick="scrollToTop()">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<script>
// Back to top button visibility
window.addEventListener('scroll', () => {
    const backToTop = document.getElementById('backToTop');
    if (window.pageYOffset > 300) {
        backToTop.classList.add('visible');
    } else {
        backToTop.classList.remove('visible');
    }
});

// Scroll to top function
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Newsletter subscription
function subscribeNewsletter(event) {
    event.preventDefault();
    const email = event.target.querySelector('input').value;
    
    // Show success message (you can replace this with actual API call)
    alert('Thank you for subscribing! 🎉');
    event.target.reset();
    
    return false;
}
</script>