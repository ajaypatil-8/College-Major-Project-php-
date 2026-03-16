<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current = basename($_SERVER['PHP_SELF']);
$pageTheme = 'orange';
if (strpos($current, 'explore') !== false || strpos($current, 'campaign') !== false) {
    $pageTheme = 'cyan';
} elseif (strpos($current, 'about') !== false) {
    $pageTheme = 'purple';
}
?>

<html lang="en" data-theme="light" data-page="<?= $pageTheme ?>">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CrowdSpark - Support Dreams, Change Lives</title>
<link rel="icon" href="/favicon.ico">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Syne:wght@700;800;900&display=swap" rel="stylesheet">

<style>

:root {
    --bg-primary: #f4f6fb;
    --bg-card: rgba(255, 255, 255, 0.92);
    --bg-card-solid: #ffffff;
    --bg-hover: #fff8ee;
    --bg-secondary: #f0f4ff;
    --bg-input: #f8faff;

    --text-primary: #0d1117;
    --text-secondary: #5a6478;
    --text-tertiary: #9aa3b2;

    --border-color: rgba(13, 17, 23, 0.07);
    --border-hover: rgba(245, 158, 11, 0.25);

    --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.09);
    --shadow-lg: 0 24px 64px rgba(0, 0, 0, 0.14);
    --shadow-xl: 0 32px 80px rgba(0, 0, 0, 0.22);

    --overlay-bg: rgba(10, 14, 26, 0.45);

    --sidebar-bg: rgba(255, 255, 255, 0.97);
    --sidebar-item-bg: #f5f7ff;
    --sidebar-item-hover: #eef1ff;
    --divider: rgba(13, 17, 23, 0.06);
}

[data-theme="dark"] {
    --bg-primary: #0c0e16;
    --bg-card: rgba(18, 20, 32, 0.96);
    --bg-card-solid: #131520;
    --bg-hover: rgba(245, 158, 11, 0.08);
    --bg-secondary: rgba(24, 26, 40, 0.85);
    --bg-input: rgba(30, 32, 50, 0.9);

    --text-primary: #e8ecf5;
    --text-secondary: #8b93aa;
    --text-tertiary: #5c647a;

    --border-color: rgba(255, 255, 255, 0.07);
    --border-hover: rgba(245, 158, 11, 0.35);

    --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.35);
    --shadow-md: 0 8px 32px rgba(0, 0, 0, 0.5);
    --shadow-lg: 0 24px 64px rgba(0, 0, 0, 0.65);
    --shadow-xl: 0 32px 80px rgba(0, 0, 0, 0.8);

    --overlay-bg: rgba(0, 0, 0, 0.7);

    --sidebar-bg: rgba(14, 16, 26, 0.98);
    --sidebar-item-bg: rgba(30, 33, 50, 0.7);
    --sidebar-item-hover: rgba(40, 44, 65, 0.9);
    --divider: rgba(255, 255, 255, 0.05);
}


[data-page="orange"] {
    --accent-primary: #f59e0b;
    --accent-secondary: #fb923c;
    --accent-light: rgba(245, 158, 11, 0.12);
    --accent-gradient: linear-gradient(135deg, #f59e0b 0%, #fb923c 100%);
    --accent-glow: rgba(245, 158, 11, 0.3);
}
[data-page="cyan"] {
    --accent-primary: #06b6d4;
    --accent-secondary: #14b8a6;
    --accent-light: rgba(6, 182, 212, 0.12);
    --accent-gradient: linear-gradient(135deg, #06b6d4 0%, #14b8a6 100%);
    --accent-glow: rgba(6, 182, 212, 0.3);
}
[data-page="purple"] {
    --accent-primary: #8b5cf6;
    --accent-secondary: #3b82f6;
    --accent-light: rgba(139, 92, 246, 0.12);
    --accent-gradient: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);
    --accent-glow: rgba(139, 92, 246, 0.3);
}

* { margin: 0; padding: 0; box-sizing: border-box; }
html, body { overflow-x: hidden; scroll-behavior: smooth; }

body {
    font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
    background: var(--bg-primary);
    color: var(--text-primary);
    padding-top: 100px;
    -webkit-font-smoothing: antialiased;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.nav-wrap {
    position: fixed;
    top: 0; left: 0;
    width: 100%;
    display: flex;
    justify-content: center;
    z-index: 999;
    padding: 18px 20px;
    animation: navSlideDown 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}

@keyframes navSlideDown {
    from { transform: translateY(-80px); opacity: 0; }
    to   { transform: translateY(0); opacity: 1; }
}

.navbar {
    max-width: 1200px;
    width: 100%;
    height: 66px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 28px;
    border-radius: 20px;
    background: var(--bg-card);
    backdrop-filter: blur(24px) saturate(200%);
    -webkit-backdrop-filter: blur(24px) saturate(200%);
    box-shadow: var(--shadow-md), 0 0 0 1px var(--border-color);
    transition: all 0.4s ease;
    position: relative;
}

.navbar::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.15);
    pointer-events: none;
}

.navbar.scrolled {
    height: 58px;
    box-shadow: var(--shadow-lg), 0 0 0 1px var(--border-color);
    border-radius: 16px;
}

/* Logo */
.logo {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 21px;
    text-decoration: none;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.3s ease;
    letter-spacing: -0.3px;
}
.logo:hover { transform: scale(1.04); }

.logo-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: var(--accent-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    box-shadow: 0 4px 12px var(--accent-glow);
    animation: iconPulse 3s ease infinite;
    flex-shrink: 0;
}

@keyframes iconPulse {
    0%, 100% { box-shadow: 0 4px 12px var(--accent-glow); }
    50% { box-shadow: 0 4px 20px var(--accent-glow), 0 0 0 4px var(--accent-light); }
}

.logo-text { color: var(--text-primary); }
.logo-text span {
    background: var(--accent-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Nav Links */
.nav-links {
    display: flex;
    align-items: center;
    gap: 2px;
    list-style: none;
}

.nav-links a {
    position: relative;
    padding: 8px 16px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-secondary);
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    gap: 7px;
    letter-spacing: 0.1px;
}

.nav-links a i {
    font-size: 13px;
    opacity: 0.7;
    transition: opacity 0.2s ease;
}

.nav-links a:hover {
    color: var(--accent-primary);
    background: var(--accent-light);
}
.nav-links a:hover i { opacity: 1; }

.nav-links a.active {
    background: var(--accent-gradient);
    color: #fff;
    box-shadow: 0 3px 12px var(--accent-glow);
}
.nav-links a.active i { opacity: 1; }
.nav-links a.active:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px var(--accent-glow);
}

/* Nav Right */
.nav-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-nav {
    padding: 9px 18px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    letter-spacing: 0.1px;
}

.btn-login {
    border: 1.5px solid var(--border-color);
    color: var(--text-secondary);
    background: transparent;
}
.btn-login:hover {
    border-color: var(--accent-primary);
    color: var(--accent-primary);
    background: var(--accent-light);
}

.btn-creator {
    background: var(--accent-gradient);
    color: #fff;
    box-shadow: 0 3px 12px var(--accent-glow);
}
.btn-creator:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px var(--accent-glow);
}

/* Theme Toggle */
.theme-btn {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    border: 1.5px solid var(--border-color);
    cursor: pointer;
    background: var(--bg-card-solid);
    color: var(--text-secondary);
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-sm);
    transition: all 0.25s ease;
}
.theme-btn:hover {
    border-color: var(--accent-primary);
    color: var(--accent-primary);
    transform: rotate(15deg) scale(1.05);
}
[data-theme="light"] .theme-btn .fa-moon { display: block; }
[data-theme="light"] .theme-btn .fa-sun  { display: none; }
[data-theme="dark"]  .theme-btn .fa-moon { display: none; }
[data-theme="dark"]  .theme-btn .fa-sun  { display: block; }

/* Avatar */
.avatar-btn {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: var(--accent-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 15px;
    cursor: pointer;
    overflow: hidden;
    box-shadow: 0 3px 12px var(--accent-glow);
    transition: all 0.25s ease;
    border: none;
    outline: none;
    position: relative;
}
.avatar-btn:hover {
    transform: translateY(-1px) scale(1.05);
    box-shadow: 0 6px 20px var(--accent-glow);
}
.avatar-btn img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* =============================================
   OVERLAY
   ============================================= */
.profile-overlay {
    position: fixed;
    inset: 0;
    background: var(--overlay-bg);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.35s ease;
    z-index: 1000;
}
.profile-overlay.active {
    opacity: 1;
    pointer-events: auto;
}

/* =============================================
   SIDEBAR — FULL REDESIGN
   ============================================= */
.profile-sidebar {
    position: fixed;
    top: 0;
    right: -500px;
    width: 380px;
    height: 100vh;
    background: var(--sidebar-bg);
    backdrop-filter: blur(40px) saturate(200%);
    -webkit-backdrop-filter: blur(40px) saturate(200%);
    box-shadow: var(--shadow-xl);
    transition: right 0.45s cubic-bezier(0.32, 0.72, 0, 1);
    z-index: 1001;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-left: 1px solid var(--border-color);
}
.profile-sidebar.active { right: 0; }

/* Close */
.sidebar-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: rgba(255,255,255,0.9);
    transition: all 0.25s ease;
    z-index: 10;
}
.sidebar-close:hover {
    background: rgba(239, 68, 68, 0.85);
    border-color: transparent;
    transform: rotate(90deg);
    color: #fff;
}

/* ---- Sidebar Header ---- */
.sidebar-header {
    padding: 0;
    background: var(--accent-gradient);
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
}

/* Decorative blobs */
.sidebar-header::before,
.sidebar-header::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}
.sidebar-header::before {
    width: 280px;
    height: 280px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 65%);
    top: -80px;
    right: -60px;
    animation: blobFloat1 7s ease-in-out infinite;
}
.sidebar-header::after {
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 65%);
    bottom: -60px;
    left: -40px;
    animation: blobFloat2 9s ease-in-out infinite;
}

@keyframes blobFloat1 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(-15px, 15px) scale(1.08); }
}
@keyframes blobFloat2 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(12px, -10px) scale(1.05); }
}

.sidebar-header-inner {
    position: relative;
    z-index: 1;
    padding: 36px 28px 28px;
    display: flex;
    flex-direction: column;
    gap: 0;
}

/* Avatar + info row */
.sidebar-profile-row {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}

.sidebar-avatar {
    width: 66px;
    height: 66px;
    border-radius: 18px;
    background: rgba(255,255,255,0.22);
    border: 2px solid rgba(255,255,255,0.35);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 900;
    font-size: 26px;
    overflow: hidden;
    flex-shrink: 0;
    box-shadow: 0 8px 28px rgba(0,0,0,0.2);
    transition: transform 0.3s ease;
}
.sidebar-avatar:hover { transform: scale(1.06); }
.sidebar-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sidebar-user-info { flex: 1; min-width: 0; }

.sidebar-user-info h4 {
    color: #fff;
    font-family: 'Syne', sans-serif;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-shadow: 0 1px 6px rgba(0,0,0,0.15);
}

.sidebar-role-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.25);
    color: rgba(255,255,255,0.95);
    font-size: 11.5px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 6px;
    letter-spacing: 0.4px;
    text-transform: uppercase;
}
.sidebar-role-badge i { font-size: 10px; }

/* Quick stats bar */
.sidebar-stats {
    display: flex;
    gap: 8px;
}

.sidebar-stat {
    flex: 1;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 12px;
    padding: 10px 10px 9px;
    text-align: center;
    backdrop-filter: blur(8px);
    transition: background 0.2s ease;
}
.sidebar-stat:hover { background: rgba(255,255,255,0.22); }

.sidebar-stat-num {
    color: #fff;
    font-family: 'Syne', sans-serif;
    font-size: 17px;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 3px;
}
.sidebar-stat-lbl {
    color: rgba(255,255,255,0.75);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}

/* ---- Sidebar Content ---- */
.sidebar-content {
    flex: 1;
    overflow-y: auto;
    padding: 20px 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.sidebar-content::-webkit-scrollbar { width: 4px; }
.sidebar-content::-webkit-scrollbar-track { background: transparent; }
.sidebar-content::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 4px;
}
.sidebar-content::-webkit-scrollbar-thumb:hover { background: var(--accent-primary); }

/* Section label */
.sidebar-section-label {
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--text-tertiary);
    padding: 12px 12px 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sidebar-section-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--divider);
}

/* Nav Items */
.sidebar-nav-item {
    text-decoration: none;
    color: var(--text-primary);
    padding: 11px 14px;
    border-radius: 13px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    background: transparent;
    border: 1px solid transparent;
}

.sidebar-nav-item .nav-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: var(--sidebar-item-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: var(--text-secondary);
    flex-shrink: 0;
    transition: all 0.25s ease;
    border: 1px solid var(--border-color);
}

.sidebar-nav-item .nav-label { flex: 1; }

.sidebar-nav-item .nav-arrow {
    font-size: 11px;
    color: var(--text-tertiary);
    opacity: 0;
    transform: translateX(-4px);
    transition: all 0.2s ease;
}

.sidebar-nav-item:hover {
    background: var(--sidebar-item-hover);
    border-color: var(--border-color);
    transform: translateX(2px);
    color: var(--accent-primary);
}
.sidebar-nav-item:hover .nav-icon {
    background: var(--accent-light);
    color: var(--accent-primary);
    border-color: var(--accent-primary);
    transform: scale(1.08);
}
.sidebar-nav-item:hover .nav-arrow {
    opacity: 1;
    transform: translateX(0);
}

/* Staggered sidebar item animation */
.profile-sidebar.active .sidebar-nav-item {
    animation: itemSlideIn 0.4s ease both;
}
.profile-sidebar.active .sidebar-nav-item:nth-child(1)  { animation-delay: 0.05s; }
.profile-sidebar.active .sidebar-nav-item:nth-child(2)  { animation-delay: 0.08s; }
.profile-sidebar.active .sidebar-nav-item:nth-child(3)  { animation-delay: 0.11s; }
.profile-sidebar.active .sidebar-nav-item:nth-child(4)  { animation-delay: 0.14s; }
.profile-sidebar.active .sidebar-nav-item:nth-child(5)  { animation-delay: 0.17s; }
.profile-sidebar.active .sidebar-nav-item:nth-child(6)  { animation-delay: 0.20s; }
.profile-sidebar.active .sidebar-nav-item:nth-child(7)  { animation-delay: 0.23s; }

@keyframes itemSlideIn {
    from { opacity: 0; transform: translateX(14px); }
    to   { opacity: 1; transform: translateX(0); }
}

/* ---- Sidebar Footer ---- */
.sidebar-footer {
    padding: 16px;
    border-top: 1px solid var(--divider);
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex-shrink: 0;
}

/* Theme toggle row */
.sidebar-theme-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 13px 14px;
    background: var(--sidebar-item-bg);
    border-radius: 13px;
    border: 1px solid var(--border-color);
    cursor: pointer;
    transition: all 0.2s ease;
}
.sidebar-theme-row:hover { border-color: var(--accent-primary); }

.sidebar-theme-left {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-primary);
    font-weight: 600;
    font-size: 14px;
}
.sidebar-theme-left i {
    font-size: 16px;
    color: var(--accent-primary);
}

/* Toggle pill */
.toggle-pill {
    width: 46px;
    height: 26px;
    background: #d1d5e0;
    border-radius: 999px;
    position: relative;
    transition: background 0.3s ease;
    cursor: pointer;
    flex-shrink: 0;
}
.toggle-pill::before {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 20px;
    height: 20px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 2px 6px rgba(0,0,0,0.18);
}
[data-theme="dark"] .toggle-pill { background: var(--accent-primary); }
[data-theme="dark"] .toggle-pill::before { transform: translateX(20px); }

/* Logout */
.sidebar-logout {
    display: flex;
    align-items: center;
    gap: 10px;
    justify-content: center;
    padding: 13px 18px;
    background: rgba(239, 68, 68, 0.08);
    border: 1.5px solid rgba(239, 68, 68, 0.18);
    color: #ef4444;
    border-radius: 13px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.25s ease;
}
.sidebar-logout i { font-size: 15px; transition: transform 0.25s ease; }
.sidebar-logout:hover {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
    transform: translateY(-1px);
}
.sidebar-logout:hover i { transform: translateX(3px); }

/* =============================================
   RESPONSIVE
   ============================================= */
@media (max-width: 900px) {
    .nav-links { display: none; }
    .navbar { padding: 0 20px; }
    .profile-sidebar { width: 340px; }
}

@media (max-width: 600px) {
    .nav-wrap { padding: 14px 12px; }
    .navbar { height: 58px; padding: 0 14px; border-radius: 16px; }
    .logo { font-size: 19px; }
    .logo-icon { width: 30px; height: 30px; font-size: 14px; }
    .btn-nav { padding: 7px 13px; font-size: 12.5px; }
    .theme-btn, .avatar-btn { width: 36px; height: 36px; border-radius: 10px; }
    .profile-sidebar { width: 100%; }
    .sidebar-header-inner { padding: 28px 20px 22px; }
    .sidebar-avatar { width: 58px; height: 58px; font-size: 22px; }
    .sidebar-user-info h4 { font-size: 16px; }
}

/* Hamburger for mobile nav */
.nav-mobile-menu {
    display: none;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: var(--bg-secondary);
    border: 1.5px solid var(--border-color);
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--text-secondary);
    font-size: 15px;
    transition: all 0.2s ease;
}
.nav-mobile-menu:hover {
    border-color: var(--accent-primary);
    color: var(--accent-primary);
}
@media (max-width: 900px) { .nav-mobile-menu { display: flex; } }

</style>


<!-- =============================================
     NAVBAR
     ============================================= -->
<div class="nav-wrap">
    <nav class="navbar" id="navbar">

        <a href="/index.php" class="logo">
            <div class="logo-icon">✨</div>
            <div class="logo-text">Crowd<span>Spark</span></div>
        </a>

        <ul class="nav-links">
            <li>
                <a class="<?= $current=='index.php'?'active':'' ?>" href="/index.php">
                    <i class="fa-solid fa-house"></i> Home
                </a>
            </li>
            <li>
                <a class="<?= (strpos($current,'explore')!==false||strpos($current,'campaign')!==false)?'active':'' ?>" href="/public/explore-campaigns.php">
                    <i class="fa-solid fa-layer-group"></i> Projects
                </a>
            </li>
            <li>
                <a class="<?= $current=='about.php'?'active':'' ?>" href="/public/about.php">
                    <i class="fa-solid fa-circle-info"></i> About
                </a>
            </li>
            <li>
                <a class="<?= $current=='contact.php'?'active':'' ?>" href="/public/contact.php">
                    <i class="fa-solid fa-phone"></i> Contact
                </a>
            </li>
        </ul>

        <div class="nav-right">

            <button class="nav-mobile-menu" onclick="openSidebar()" title="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <button class="theme-btn" onclick="toggleTheme()" title="Toggle theme">
                <i class="fa-solid fa-moon"></i>
                <i class="fa-solid fa-sun"></i>
            </button>

            <?php if(!isset($_SESSION['user_id'])): ?>

                <a href="/user/login.php" class="btn-nav btn-login">
                    <i class="fa-solid fa-sign-in-alt"></i> Login
                </a>
                <a href="/user/becomecreator.php" class="btn-nav btn-creator">
                    <i class="fa-solid fa-rocket"></i> Start Project
                </a>

            <?php else: ?>

                <?php if($_SESSION['role']=="creator"): ?>
                    <a href="/creator/create-campaign.php" class="btn-nav btn-creator">
                        <i class="fa-solid fa-plus"></i> New Campaign
                    </a>
                <?php elseif($_SESSION['role']=="admin"): ?>
                    <a href="/admin/admin-dashboard.php" class="btn-nav btn-creator">
                        <i class="fa-solid fa-shield"></i> Admin
                    </a>
                <?php else: ?>
                    <a href="/user/becomecreator.php" class="btn-nav btn-creator">
                        <i class="fa-solid fa-star"></i> Become Creator
                    </a>
                <?php endif; ?>

                <button class="avatar-btn" onclick="openSidebar()">
                    <?php if(!empty($_SESSION['profile_image'])): ?>
                        <img src="<?= $_SESSION['profile_image'] ?>" alt="Profile">
                    <?php else: ?>
                        <?= strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                    <?php endif; ?>
                </button>

            <?php endif; ?>

        </div>
    </nav>
</div>


<!-- =============================================
     SIDEBAR
     ============================================= -->
<?php if(isset($_SESSION['user_id'])): ?>

<div id="overlay" class="profile-overlay" onclick="closeSidebar()"></div>

<div id="sidebar" class="profile-sidebar">

    <button class="sidebar-close" onclick="closeSidebar()" title="Close">
        <i class="fa-solid fa-times"></i>
    </button>

    <!-- Header -->
    <div class="sidebar-header">
        <div class="sidebar-header-inner">

            <div class="sidebar-profile-row">
                <div class="sidebar-avatar">
                    <?php if(!empty($_SESSION['profile_image'])): ?>
                        <img src="<?= $_SESSION['profile_image'] ?>" alt="Profile">
                    <?php else: ?>
                        <?= strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>

                <div class="sidebar-user-info">
                    <h4><?= htmlspecialchars($_SESSION['name']); ?></h4>
                    <div class="sidebar-role-badge">
                        <?php if($_SESSION['role']=='admin'): ?>
                            <i class="fa-solid fa-shield"></i>
                        <?php elseif($_SESSION['role']=='creator'): ?>
                            <i class="fa-solid fa-bolt"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-user"></i>
                        <?php endif; ?>
                        <?= ucfirst($_SESSION['role']); ?>
                    </div>
                </div>
            </div>

            <!-- Quick stats -->
            <div class="sidebar-stats">
                <div class="sidebar-stat">
                    <div class="sidebar-stat-num">—</div>
                    <div class="sidebar-stat-lbl">Backed</div>
                </div>
                <?php if($_SESSION['role']=='creator'): ?>
                <div class="sidebar-stat">
                    <div class="sidebar-stat-num">—</div>
                    <div class="sidebar-stat-lbl">Projects</div>
                </div>
                <?php endif; ?>
                <div class="sidebar-stat">
                    <div class="sidebar-stat-num">—</div>
                    <div class="sidebar-stat-lbl">Saved</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Content -->
    <div class="sidebar-content">

        <?php if($_SESSION['role']=="admin"): ?>
            <div class="sidebar-section-label">Administration</div>
            <a href="/admin/admin-dashboard.php" class="sidebar-nav-item">
                <div class="nav-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <span class="nav-label">Admin Dashboard</span>
                <i class="fa-solid fa-chevron-right nav-arrow"></i>
            </a>
        <?php endif; ?>

        <?php if($_SESSION['role']=="creator"): ?>
            <div class="sidebar-section-label">Creator</div>
            <a href="/creator/creator-dashboard.php" class="sidebar-nav-item">
                <div class="nav-icon"><i class="fa-solid fa-chart-line"></i></div>
                <span class="nav-label">Creator Dashboard</span>
                <i class="fa-solid fa-chevron-right nav-arrow"></i>
            </a>
            <a href="/creator/my-campaigns.php" class="sidebar-nav-item">
                <div class="nav-icon"><i class="fa-solid fa-layer-group"></i></div>
                <span class="nav-label">My Campaigns</span>
                <i class="fa-solid fa-chevron-right nav-arrow"></i>
            </a>
        <?php endif; ?>

        <div class="sidebar-section-label">Account</div>

        <a href="/dashboard/user-dashboard.php" class="sidebar-nav-item">
            <div class="nav-icon"><i class="fa-solid fa-grid-2"></i></div>
            <span class="nav-label">My Dashboard</span>
            <i class="fa-solid fa-chevron-right nav-arrow"></i>
        </a>
        <a href="/dashboard/edit-profile.php" class="sidebar-nav-item">
            <div class="nav-icon"><i class="fa-solid fa-user-pen"></i></div>
            <span class="nav-label">Edit Profile</span>
            <i class="fa-solid fa-chevron-right nav-arrow"></i>
        </a>
        <a href="/dashboard/change-password.php" class="sidebar-nav-item">
            <div class="nav-icon"><i class="fa-solid fa-lock"></i></div>
            <span class="nav-label">Change Password</span>
            <i class="fa-solid fa-chevron-right nav-arrow"></i>
        </a>
        <a href="/dashboard/my-donations.php" class="sidebar-nav-item">
            <div class="nav-icon"><i class="fa-solid fa-heart"></i></div>
            <span class="nav-label">My Donations</span>
            <i class="fa-solid fa-chevron-right nav-arrow"></i>
        </a>

    </div>

    <!-- Footer -->
    <div class="sidebar-footer">

        <div class="sidebar-theme-row" onclick="toggleTheme()">
            <div class="sidebar-theme-left">
                <i class="fa-solid fa-circle-half-stroke"></i>
                <span>Dark Mode</span>
            </div>
            <div class="toggle-pill"></div>
        </div>

        <a href="/user/logout.php" class="sidebar-logout">
            <i class="fa-solid fa-right-from-bracket"></i>
            Sign Out
        </a>

    </div>

</div>

<?php endif; ?>
</head>

<body>

<script>

function getTheme() {
    return localStorage.getItem('crowdspark-theme') || 'light';
}
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('crowdspark-theme', theme);
}
function toggleTheme() {
    setTheme(getTheme() === 'light' ? 'dark' : 'light');
}
(function() { setTheme(getTheme()); })();


function openSidebar() {
    document.getElementById('sidebar').classList.add('active');
    document.getElementById('overlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('active');
    document.getElementById('overlay').classList.remove('active');
    document.body.style.overflow = '';
}


window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.pageYOffset > 50);
});
</script>