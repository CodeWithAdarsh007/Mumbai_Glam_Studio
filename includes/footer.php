<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand -->
            <div class="footer-brand">
                <div class="logo-text" style="font-size:1.2rem; margin-bottom:12px;">
                    Mumbai Glam
                    <span style="font-size:0.55em; opacity:0.6;">Studio</span>
                </div>
                <p>Mumbai's trusted salon marketplace. Discover, compare, and book the city's best beauty studios — all in one place.</p>
            </div>

            <!-- Quick Links -->
            <div>
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="salons.php">All Salons</a></li>
                    <li><a href="salons.php?locality=Andheri">Andheri Salons</a></li>
                    <li><a href="salons.php?locality=Bandra">Bandra Salons</a></li>
                    <li><a href="salons.php?locality=Dadar">Dadar Salons</a></li>
                </ul>
            </div>

            <!-- Join Us -->
            <div>
                <h4>Join Us</h4>
                <ul class="footer-links">
                    <li><a href="register.php">Register</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4>Contact</h4>
                <ul class="footer-links">
                    <li>
                        <i class="fas fa-phone" style="margin-right:6px;font-size:0.7rem;width:16px;"></i>
                        +91 98765 43210
                    </li>
                    <li>
                        <i class="fas fa-envelope" style="margin-right:6px;font-size:0.7rem;width:16px;"></i>
                        hello@mumbai-glam-studio.infinityfree.me
                    </li>
                    <li>
                        <i class="fas fa-map-marker-alt" style="margin-right:6px;font-size:0.7rem;width:16px;"></i>
                        Andheri West, Mumbai
                    </li>
                </ul>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
            &copy; <?php echo date('Y'); ?> Mumbai Glam Studio. All rights reserved. Crafted with care in Mumbai.
        </div>
    </div>
</footer>

<style>
    /* --- Footer dark mode fixes --- */
    .footer {
        background: var(--charcoal);
        color: rgba(255,255,255,0.7);
        padding: clamp(40px, 6vw, 60px) 0 24px;
    }

    [data-theme="dark"] .footer {
        background: #0d0d0d;
    }

    .footer-brand .logo-text {
        color: var(--text-light);
        margin-bottom: 12px;
    }

    .footer-brand p {
        font-size: 0.82rem;
        line-height: 1.5;
        opacity: 0.7;
        color: rgba(255,255,255,0.7);
    }

    .footer h4 {
        color: var(--text-light);
        font-size: 0.9rem;
        margin-bottom: 16px;
        font-family: var(--font-body);
        font-weight: 600;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 8px;
        color: rgba(255,255,255,0.6);
        font-size: 0.82rem;
        display: flex;
        align-items: center;
    }

    .footer-links a {
        color: rgba(255,255,255,0.6);
        font-size: 0.82rem;
        transition: color var(--transition-fast);
        text-decoration: none;
    }

    .footer-links a:hover {
        color: var(--gold);
    }

    .footer-bottom {
        border-top: 1px solid rgba(255,255,255,0.1);
        padding-top: 20px;
        text-align: center;
        font-size: 0.78rem;
        opacity: 0.5;
        color: rgba(255,255,255,0.5);
    }

    /* Ensure icons in footer are visible in dark mode */
    .footer-links i {
        color: rgba(255,255,255,0.5);
    }
</style>