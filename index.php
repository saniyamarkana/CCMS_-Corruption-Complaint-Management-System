<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCMS — Corruption Complaint Management System | Secure, Anonymous & Transparent</title>
    <meta name="description" content="Next-Generation National Anti-Corruption & Whistleblower Complaint Platform with 256-bit encryption, real-time case tracking, and automated forensic oversight.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ══════════════════════ DESIGN SYSTEM TOKENS ══════════════════════ */
        :root {
            --bg-base: #05070e;
            --bg-surface: #0a0f1e;
            --bg-card: rgba(14, 20, 42, 0.7);
            --bg-card-hover: rgba(22, 32, 65, 0.85);
            --bg-glass: rgba(12, 18, 35, 0.55);
            --border-color: rgba(99, 102, 241, 0.15);
            --border-glow: rgba(99, 102, 241, 0.5);

            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4338ca;
            --primary-glow: rgba(99, 102, 241, 0.35);

            --cyan: #06b6d4;
            --cyan-glow: rgba(6, 182, 212, 0.35);
            --emerald: #10b981;
            --emerald-glow: rgba(16, 185, 129, 0.35);
            --amber: #f59e0b;
            --rose: #f43f5e;
            --violet: #8b5cf6;

            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --text-subtle: #64748b;

            --header-bg: rgba(5, 7, 14, 0.7);
            --modal-overlay: rgba(3, 5, 12, 0.88);
            --card-shadow: 0 25px 50px -15px rgba(0, 0, 0, 0.65);
            --card-glow-shadow: 0 0 40px -5px rgba(99, 102, 241, 0.2);

            --font-sans: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-display: 'Space Grotesk', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --transition-smooth: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            --transition-bounce: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        [data-theme="light"] {
            --bg-base: #f0f2f8;
            --bg-surface: #ffffff;
            --bg-card: rgba(255, 255, 255, 0.85);
            --bg-card-hover: rgba(255, 255, 255, 0.98);
            --bg-glass: rgba(255, 255, 255, 0.7);
            --border-color: rgba(99, 102, 241, 0.12);
            --border-glow: rgba(99, 102, 241, 0.35);
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #3730a3;
            --primary-glow: rgba(79, 70, 229, 0.18);
            --cyan: #0891b2;
            --cyan-glow: rgba(8, 145, 178, 0.18);
            --emerald: #059669;
            --emerald-glow: rgba(5, 150, 105, 0.18);
            --text-main: #0f172a;
            --text-muted: #475569;
            --text-subtle: #94a3b8;
            --header-bg: rgba(240, 242, 248, 0.8);
            --modal-overlay: rgba(15, 23, 42, 0.6);
            --card-shadow: 0 25px 50px -15px rgba(99, 102, 241, 0.08);
            --card-glow-shadow: 0 10px 30px -5px rgba(99, 102, 241, 0.12);
        }

        [data-theme="emerald"] {
            --bg-base: #040f0b;
            --bg-surface: #081f17;
            --bg-card: rgba(8, 31, 23, 0.75);
            --bg-card-hover: rgba(12, 45, 34, 0.9);
            --bg-glass: rgba(8, 31, 23, 0.6);
            --border-color: rgba(16, 185, 129, 0.2);
            --border-glow: rgba(16, 185, 129, 0.5);
            --primary: #10b981;
            --primary-light: #34d399;
            --primary-dark: #059669;
            --primary-glow: rgba(16, 185, 129, 0.3);
            --cyan: #06b6d4;
            --cyan-glow: rgba(6, 182, 212, 0.3);
            --text-main: #ecfdf5;
            --text-muted: #86efac;
            --text-subtle: #4ade80;
            --header-bg: rgba(4, 15, 11, 0.75);
            --modal-overlay: rgba(2, 8, 5, 0.88);
            --card-shadow: 0 25px 50px -15px rgba(0, 0, 0, 0.6);
            --card-glow-shadow: 0 0 40px -5px rgba(16, 185, 129, 0.2);
        }

        /* ══════════════════════ RESET & BASE ══════════════════════ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 100px;
        }

        body {
            font-family: var(--font-sans);
            background: var(--bg-base);
            color: var(--text-main);
            overflow-x: hidden;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        *, body, div, main, aside, nav, ul, table, textarea {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        ::-webkit-scrollbar { display: none !important; }

        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }
        img { max-width: 100%; display: block; }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-display);
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1.15;
        }

        .container {
            max-width: 1260px;
            margin: 0 auto;
            padding: 0 1.5rem;
            position: relative;
            z-index: 2;
        }

        /* ══════════════════════ AMBIENT BACKGROUND ══════════════════════ */
        .ambient-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            z-index: 0;
            opacity: 0.5;
            transition: opacity 1s ease;
        }
        .ambient-orb-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(99,102,241,0.3), transparent 70%);
            top: -10%; left: -8%;
            animation: orbFloat1 18s ease-in-out infinite;
        }
        .ambient-orb-2 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(6,182,212,0.25), transparent 70%);
            top: 30%; right: -10%;
            animation: orbFloat2 22s ease-in-out infinite;
        }
        .ambient-orb-3 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(139,92,246,0.2), transparent 70%);
            bottom: 10%; left: 20%;
            animation: orbFloat3 20s ease-in-out infinite;
        }

        @keyframes orbFloat1 { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(60px, 40px) scale(1.1); } }
        @keyframes orbFloat2 { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(-50px, 50px) scale(1.15); } }
        @keyframes orbFloat3 { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(40px, -30px) scale(1.05); } }

        .grid-overlay {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
            z-index: 0;
        }
        [data-theme="light"] .grid-overlay {
            background-image:
                linear-gradient(rgba(0,0,0,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,0,0,0.03) 1px, transparent 1px);
        }

        /* ══════════════════════ UTILITY CLASSES ══════════════════════ */
        .gradient-text {
            background: linear-gradient(135deg, #ffffff 0%, var(--primary-light) 40%, var(--cyan) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        [data-theme="light"] .gradient-text {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 40%, var(--cyan) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        [data-theme="emerald"] .gradient-text {
            background: linear-gradient(135deg, #ecfdf5 0%, var(--primary-light) 40%, var(--cyan) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid var(--border-color);
            color: var(--primary-light);
            backdrop-filter: blur(8px);
        }

        .live-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--emerald);
            box-shadow: 0 0 12px var(--emerald);
            animation: liveBlink 1.8s infinite;
        }
        @keyframes liveBlink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }

        .section-header {
            text-align: center;
            max-width: 720px;
            margin: 0 auto 4rem;
        }
        .section-header .badge-pill { margin-bottom: 1.25rem; }
        .section-title {
            font-size: clamp(2rem, 4vw, 3rem);
            margin-bottom: 1rem;
            letter-spacing: -0.04em;
        }
        .section-subtitle {
            font-size: 1.05rem;
            color: var(--text-muted);
            line-height: 1.7;
        }

        /* ══════════════════════ BUTTONS ══════════════════════ */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.85rem 1.75rem;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: var(--font-sans);
            border-radius: 14px;
            cursor: pointer;
            transition: var(--transition-smooth);
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #ffffff;
            box-shadow: 0 8px 25px -5px var(--primary-glow);
        }
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.6s;
            z-index: -1;
        }
        .btn-primary:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 16px 40px -5px var(--primary-glow);
        }
        .btn-primary:hover::before { left: 100%; }

        .btn-secondary {
            background: var(--bg-glass);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(12px);
        }
        .btn-secondary:hover {
            background: var(--bg-card-hover);
            border-color: var(--border-glow);
            color: var(--primary-light);
            transform: translateY(-3px);
            box-shadow: var(--card-glow-shadow);
        }

        .btn-cyan {
            background: linear-gradient(135deg, var(--cyan) 0%, #0891b2 100%);
            color: #ffffff;
            box-shadow: 0 8px 25px -5px var(--cyan-glow);
        }
        .btn-cyan:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 16px 40px -5px var(--cyan-glow);
        }

        /* Ripple click effect */
        .btn .ripple-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.35);
            transform: scale(0);
            animation: rippleExpand 0.6s ease-out;
            pointer-events: none;
        }
        @keyframes rippleExpand { to { transform: scale(4); opacity: 0; } }

        /* ══════════════════════ PREMIUM GLASSMORPHIC FLOATING NAVBAR ══════════════════════ */
        header {
            position: fixed;
            top: 14px;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 40px);
            max-width: 1300px;
            z-index: 1000;
            background: rgba(5, 7, 14, 0.65);
            backdrop-filter: blur(28px) saturate(2);
            -webkit-backdrop-filter: blur(28px) saturate(2);
            border: 1px solid rgba(99, 102, 241, 0.18);
            border-radius: 24px;
            transition: var(--transition-smooth);
            box-shadow:
                0 4px 6px -1px rgba(0,0,0,0.5),
                0 16px 48px -8px rgba(0,0,0,0.4),
                inset 0 1px 0 rgba(255,255,255,0.06);
        }
        header::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 24px;
            background: linear-gradient(135deg,
                rgba(99,102,241,0.05) 0%,
                rgba(6,182,212,0.03) 50%,
                rgba(139,92,246,0.05) 100%);
            pointer-events: none;
        }
        header.scrolled {
            top: 8px;
            background: rgba(5, 7, 14, 0.88);
            box-shadow:
                0 4px 6px -1px rgba(0,0,0,0.6),
                0 20px 60px -8px rgba(0,0,0,0.55),
                0 0 0 1px rgba(99,102,241,0.25),
                inset 0 1px 0 rgba(255,255,255,0.07);
        }
        [data-theme="light"] header {
            background: rgba(240, 242, 248, 0.72);
            border-color: rgba(99, 102, 241, 0.12);
            box-shadow: 0 4px 24px rgba(99,102,241,0.08), inset 0 1px 0 rgba(255,255,255,0.9);
        }
        [data-theme="light"] header.scrolled {
            background: rgba(240, 242, 248, 0.92);
            box-shadow: 0 8px 40px rgba(99,102,241,0.12), 0 0 0 1px rgba(99,102,241,0.15), inset 0 1px 0 rgba(255,255,255,1);
        }
        [data-theme="emerald"] header {
            background: rgba(4, 15, 11, 0.65);
            border-color: rgba(16, 185, 129, 0.18);
        }
        [data-theme="emerald"] header.scrolled {
            background: rgba(4, 15, 11, 0.88);
            box-shadow: 0 20px 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(16,185,129,0.25);
        }


        /* ── Navbar Layout ── */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 72px;
            padding: 0 1.5rem;
            gap: 1rem;
        }

        /* ── Brand Logo ── */
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
            color: var(--text-main);
            flex-shrink: 0;
            position: relative;
        }
        .logo-icon-box {
            width: 44px; height: 44px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--cyan) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.2rem;
            position: relative;
            transition: var(--transition-smooth);
            box-shadow: 0 0 0 0 var(--primary-glow);
            flex-shrink: 0;
        }
        .logo-icon-box::after {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--primary), var(--cyan));
            z-index: -1;
            opacity: 0;
            filter: blur(8px);
            transition: opacity 0.4s ease;
        }
        .brand-logo:hover .logo-icon-box {
            transform: rotate(-8deg) scale(1.08);
            box-shadow: 0 0 30px var(--primary-glow);
        }
        .brand-logo:hover .logo-icon-box::after { opacity: 0.7; }
        .logo-shield-pulse {
            position: absolute;
            inset: -4px;
            border-radius: 18px;
            border: 2px solid var(--primary);
            opacity: 0;
            animation: shieldPulse 3s ease-in-out infinite;
        }
        @keyframes shieldPulse {
            0%, 100% { opacity: 0; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.12); }
        }
        .brand-text {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .brand-text h1 {
            font-size: 1.3rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, var(--text-main) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }
        [data-theme="light"] .brand-text h1 {
            background: linear-gradient(135deg, #0f172a 0%, var(--primary) 100%);
            -webkit-background-clip: text;
            background-clip: text;
        }
        .brand-text .brand-sub {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--cyan);
            display: block;
            margin-top: 1px;
            font-family: var(--font-mono);
        }

        /* Separator */
        .nav-separator {
            width: 1px;
            height: 28px;
            background: linear-gradient(to bottom, transparent, var(--border-color), transparent);
            flex-shrink: 0;
        }

        /* ── Nav Links ── */
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 0.2rem;
            flex: 1;
            justify-content: center;
        }
        .nav-link {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
            transition: var(--transition-smooth);
            position: relative;
            padding: 0.5rem 0.9rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.42rem;
            white-space: nowrap;
        }
        .nav-link i {
            font-size: 0.8rem;
            opacity: 0.7;
            transition: var(--transition-smooth);
        }
        /* ── Nav Links hover: center-expanding underline ── */
        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%) scaleX(0);
            transform-origin: center;
            width: calc(100% - 1.4rem);
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--cyan), var(--primary));
            background-size: 200% 100%;
            border-radius: 9999px;
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                        box-shadow 0.35s ease;
            box-shadow: none;
        }
        .nav-link:hover::before {
            transform: translateX(-50%) scaleX(1);
            box-shadow: 0 0 10px var(--primary-glow);
            animation: underlineShimmer 1.8s linear infinite;
        }
        @keyframes underlineShimmer {
            0%   { background-position: 0% 0%; }
            100% { background-position: 200% 0%; }
        }

        .nav-link:hover {
            color: var(--primary-light);
            background: rgba(99, 102, 241, 0.06);
        }
        .nav-link:hover i {
            opacity: 1;
            color: var(--primary-light);
            transform: translateY(-1px);
        }

        /* Active state — underline always shown via ::after */
        .nav-link.active {
            color: var(--primary-light);
            background: rgba(99, 102, 241, 0.09);
            font-weight: 600;
        }
        .nav-link.active i { opacity: 1; color: var(--primary-light); }

        /* Active underline (persistent) */
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%) scaleX(1);
            transform-origin: center;
            width: calc(100% - 1.4rem);
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--cyan));
            border-radius: 9999px;
            box-shadow: 0 0 10px var(--primary-glow);
        }
        /* Active state — hide the hover ::before so they don't stack */
        .nav-link.active::before { display: none; }

        [data-theme="light"] .nav-link:hover { background: rgba(99, 102, 241, 0.05); }
        [data-theme="emerald"] .nav-link.active { background: rgba(16,185,129,0.1); }
        [data-theme="emerald"] .nav-link::before,
        [data-theme="emerald"] .nav-link.active::after {
            background: linear-gradient(90deg, var(--primary), var(--cyan));
        }

        /* ── Nav Actions ── */
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            flex-shrink: 0;
        }

        /* ── Theme Switcher ── */
        .theme-switcher {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-color);
            border-radius: 9999px;
            padding: 3px;
            gap: 1px;
        }
        [data-theme="light"] .theme-switcher {
            background: rgba(0,0,0,0.04);
        }
        .theme-btn {
            background: transparent;
            border: none;
            color: var(--text-subtle);
            width: 30px; height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.78rem;
            transition: var(--transition-smooth);
            position: relative;
        }
        .theme-btn::after {
            content: attr(title);
            position: absolute;
            bottom: -32px;
            left: 50%;
            transform: translateX(-50%) scale(0.85);
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 0.65rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: 0.2s ease;
            z-index: 100;
        }
        .theme-btn:hover::after { opacity: 1; transform: translateX(-50%) scale(1); }
        .theme-btn.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #ffffff;
            box-shadow: 0 0 14px var(--primary-glow);
        }
        .theme-btn:hover:not(.active) {
            color: var(--text-main);
            background: rgba(255,255,255,0.07);
        }

        /* ── CTA Button ── */
        .nav-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.55rem 1.2rem;
            font-size: 0.85rem;
            font-weight: 700;
            font-family: var(--font-sans);
            border-radius: 14px;
            cursor: pointer;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow:
                0 4px 15px -3px var(--primary-glow),
                inset 0 1px 0 rgba(255,255,255,0.15);
            transition: var(--transition-smooth);
            white-space: nowrap;
        }
        .nav-cta::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.18), transparent);
            transition: 0.55s ease;
        }
        .nav-cta:hover {
            transform: translateY(-2px) scale(1.03);
            box-shadow:
                0 8px 28px -5px var(--primary-glow),
                inset 0 1px 0 rgba(255,255,255,0.2);
        }
        .nav-cta:hover::before { left: 100%; }
        .nav-cta i { font-size: 0.82rem; }

        /* ── Mobile Toggle ── */
        .mobile-toggle {
            display: none;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            width: 40px; height: 40px;
            border-radius: 12px;
            font-size: 1rem;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            transition: var(--transition-smooth);
            flex-shrink: 0;
        }
        .mobile-toggle:hover {
            background: rgba(99,102,241,0.1);
            border-color: var(--border-glow);
            color: var(--primary-light);
        }

        /* ══════════════════════ HERO SECTION ══════════════════════ */
        .hero-section {
            padding: 10rem 0 5rem;
            position: relative;
            overflow: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .hero-bg-mesh {
            position: absolute;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }
        .hero-bg-mesh::before {
            content: '';
            position: absolute;
            width: 140%;
            height: 140%;
            top: -20%;
            left: -20%;
            background:
                radial-gradient(ellipse 600px 500px at 25% 30%, rgba(99,102,241,0.2) 0%, transparent 70%),
                radial-gradient(ellipse 500px 400px at 75% 60%, rgba(6,182,212,0.15) 0%, transparent 70%),
                radial-gradient(ellipse 300px 300px at 50% 80%, rgba(139,92,246,0.12) 0%, transparent 70%);
            animation: meshRotate 30s linear infinite;
        }
        @keyframes meshRotate { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        #particleCanvas {
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
        }

        .hero-content-wrapper {
            position: relative;
            z-index: 3;
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
        }

        .hero-badge {
            animation: fadeSlideDown 0.8s ease-out;
        }

        .hero-title {
            font-size: clamp(2.8rem, 6.5vw, 4.5rem);
            font-weight: 900;
            letter-spacing: -0.05em;
            line-height: 1.08;
            margin: 1.75rem 0 1.5rem;
            animation: fadeSlideDown 0.8s ease-out 0.15s both;
        }

        .typewriter-word {
            position: relative;
            color: var(--cyan);
            -webkit-text-fill-color: var(--cyan);
        }
        .typewriter-word::after {
            content: '|';
            animation: cursorBlink 0.8s infinite;
            margin-left: 2px;
            font-weight: 300;
            color: var(--cyan);
            -webkit-text-fill-color: var(--cyan);
        }
        @keyframes cursorBlink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

        .hero-description {
            font-size: 1.15rem;
            color: var(--text-muted);
            max-width: 680px;
            margin: 0 auto 2.5rem;
            line-height: 1.75;
            animation: fadeSlideDown 0.8s ease-out 0.3s both;
        }

        .hero-cta-group {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 3.5rem;
            animation: fadeSlideDown 0.8s ease-out 0.45s both;
        }

        .hero-stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            max-width: 640px;
            margin: 0 auto;
            animation: fadeSlideDown 0.8s ease-out 0.6s both;
        }
        .hero-stat-item {
            text-align: center;
            padding: 1.5rem 1rem;
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            backdrop-filter: blur(12px);
            transition: var(--transition-smooth);
        }
        .hero-stat-item:hover {
            border-color: var(--border-glow);
            transform: translateY(-4px);
            box-shadow: var(--card-glow-shadow);
        }
        .hero-stat-item h3 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-light), var(--cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-stat-item p {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-top: 0.25rem;
        }

        /* Orbiting Shield Icons */
        .orbit-container {
            position: absolute;
            width: 700px;
            height: 700px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            pointer-events: none;
        }
        .orbit-ring {
            position: absolute;
            inset: 0;
            border: 1px dashed rgba(99, 102, 241, 0.1);
            border-radius: 50%;
            animation: orbitSpin 40s linear infinite;
        }
        .orbit-icon {
            position: absolute;
            width: 44px; height: 44px;
            border-radius: 12px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-light);
            font-size: 1rem;
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .orbit-icon:nth-child(2) { top: 0; left: 50%; transform: translateX(-50%); color: var(--cyan); }
        .orbit-icon:nth-child(3) { top: 50%; right: 0; transform: translateY(-50%); color: var(--emerald); }
        .orbit-icon:nth-child(4) { bottom: 0; left: 50%; transform: translateX(-50%); color: var(--violet); }
        .orbit-icon:nth-child(5) { top: 50%; left: 0; transform: translateY(-50%); color: var(--amber); }
        @keyframes orbitSpin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        @keyframes fadeSlideDown {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ══════════════════════ MARQUEE TICKER ══════════════════════ */
        .marquee-section {
            padding: 1.25rem 0;
            overflow: hidden;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            background: rgba(99, 102, 241, 0.03);
            position: relative;
            z-index: 2;
        }
        .marquee-track {
            display: flex;
            gap: 2.5rem;
            animation: marqueeScroll 35s linear infinite;
            white-space: nowrap;
            width: max-content;
        }
        .marquee-item {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.5rem 1.25rem;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: var(--text-muted);
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 9999px;
            backdrop-filter: blur(8px);
            white-space: nowrap;
        }
        .marquee-item i { color: var(--primary-light); font-size: 0.9rem; }
        @keyframes marqueeScroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

        /* ══════════════════════ BENTO GRID: SECURITY FEATURES ══════════════════════ */
        .security-section {
            padding: 7rem 0;
            position: relative;
            z-index: 2;
        }

        .bento-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: auto auto;
            gap: 1.25rem;
        }
        .bento-grid .bento-card:nth-child(1) { grid-column: span 2; }
        .bento-grid .bento-card:nth-child(4) { grid-column: span 2; }

        .bento-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            transition: var(--transition-smooth);
            cursor: default;
            backdrop-filter: blur(8px);
        }
        .bento-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            padding: 1px;
            background: linear-gradient(135deg, transparent, rgba(99,102,241,0.3), transparent);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }
        .bento-card:hover::before { opacity: 1; }
        .bento-card:hover {
            background: var(--bg-card-hover);
            transform: translateY(-6px);
            box-shadow: var(--card-shadow), var(--card-glow-shadow);
            border-color: var(--border-glow);
        }

        .bento-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(6,182,212,0.1));
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: var(--primary-light);
            margin-bottom: 1.25rem;
            transition: var(--transition-smooth);
        }
        .bento-card:hover .bento-icon {
            transform: scale(1.1) rotate(-5deg);
            box-shadow: 0 0 25px var(--primary-glow);
            border-color: var(--border-glow);
        }

        .bento-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.65rem;
            letter-spacing: -0.02em;
        }
        .bento-card p {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.65;
            margin-bottom: 1.25rem;
        }
        .bento-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .bento-tag {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.3rem 0.7rem;
            border-radius: 8px;
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            transition: var(--transition-smooth);
        }
        .bento-card:hover .bento-tag {
            border-color: var(--border-glow);
            color: var(--primary-light);
        }

        /* Mouse glow effect overlay on bento cards */
        .bento-card .glow-spot {
            position: absolute;
            width: 250px; height: 250px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99,102,241,0.12), transparent 70%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            transform: translate(-50%, -50%);
            z-index: 0;
        }
        .bento-card:hover .glow-spot { opacity: 1; }
        .bento-card > * { position: relative; z-index: 1; }

        /* ══════════════════════ ANIMATED PROCESS TIMELINE ══════════════════════ */
        .process-section {
            padding: 7rem 0;
            position: relative;
            z-index: 2;
        }

        .timeline-wrapper {
            max-width: 720px;
            margin: 0 auto;
            position: relative;
        }
        .timeline-line {
            position: absolute;
            left: 28px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--border-color);
            border-radius: 3px;
        }
        .timeline-line-fill {
            position: absolute;
            left: 28px;
            top: 0;
            width: 3px;
            height: 0%;
            background: linear-gradient(180deg, var(--primary), var(--cyan));
            border-radius: 3px;
            transition: height 0.8s ease-out;
            box-shadow: 0 0 12px var(--primary-glow);
        }

        .timeline-step {
            display: flex;
            gap: 1.75rem;
            margin-bottom: 2.5rem;
            position: relative;
            opacity: 0;
            transform: translateX(-20px);
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .timeline-step.visible {
            opacity: 1;
            transform: translateX(0);
        }

        .timeline-node {
            width: 56px; height: 56px;
            border-radius: 16px;
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-subtle);
            flex-shrink: 0;
            transition: var(--transition-smooth);
            z-index: 2;
        }
        .timeline-step.visible .timeline-node {
            background: linear-gradient(135deg, var(--primary), var(--cyan));
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 0 25px var(--primary-glow);
        }

        .timeline-step-content {
            flex: 1;
            padding: 1.25rem 1.5rem;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            transition: var(--transition-smooth);
        }
        .timeline-step:hover .timeline-step-content {
            border-color: var(--border-glow);
            background: var(--bg-card-hover);
            transform: translateX(6px);
            box-shadow: var(--card-glow-shadow);
        }
        .timeline-step-content h3 {
            font-size: 1.1rem;
            margin-bottom: 0.4rem;
        }
        .timeline-step-content p {
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* ══════════════════════ LIVE ANALYTICS RADAR ══════════════════════ */
        .analytics-section {
            padding: 7rem 0;
            position: relative;
            z-index: 2;
        }

        .dashboard-glass {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 2.5rem;
            backdrop-filter: blur(16px);
            box-shadow: var(--card-shadow);
        }

        .radar-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .radar-top-bar h2 {
            font-size: 1.6rem;
        }

        .radar-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        .radar-kpi {
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition-smooth);
        }
        .radar-kpi:hover {
            border-color: var(--border-glow);
            transform: translateY(-4px);
            box-shadow: var(--card-glow-shadow);
        }
        .radar-kpi-value {
            font-family: var(--font-display);
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-light), var(--cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .radar-kpi-tag {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            margin-top: 0.35rem;
        }
        .growth-up { background: rgba(16,185,129,0.12); color: var(--emerald); }
        .growth-cyan { background: rgba(6,182,212,0.12); color: var(--cyan); }
        .radar-kpi-label {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
            font-weight: 500;
        }

        .live-stream-box {
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            overflow: hidden;
        }
        .stream-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        .stream-header h4 {
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .stream-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            transition: var(--transition-smooth);
            gap: 1rem;
        }
        .stream-row:last-child { border-bottom: none; }
        .stream-row:hover { background: rgba(99, 102, 241, 0.04); }
        .stream-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.88rem;
            color: var(--text-muted);
            flex: 1;
        }
        .stream-badge {
            font-family: var(--font-mono);
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--primary-light);
            white-space: nowrap;
        }
        .stream-time {
            font-size: 0.75rem;
            color: var(--text-subtle);
            font-family: var(--font-mono);
            white-space: nowrap;
        }

        /* ══════════════════════ TESTIMONIAL CAROUSEL ══════════════════════ */
        .testimonials-section {
            padding: 7rem 0;
            position: relative;
            z-index: 2;
            overflow: hidden;
        }

        .carousel-viewport {
            overflow: hidden;
            border-radius: 20px;
        }
        .carousel-track {
            display: flex;
            transition: transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .carousel-slide {
            min-width: 100%;
            padding: 0 1rem;
        }

        .testimonial-inner-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        .testimonial-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
        }
        .testimonial-card::before {
            content: '\201C';
            position: absolute;
            top: 10px; right: 20px;
            font-size: 5rem;
            font-family: Georgia, serif;
            color: var(--primary);
            opacity: 0.08;
            line-height: 1;
        }
        .testimonial-card:hover {
            border-color: var(--border-glow);
            transform: translateY(-6px);
            box-shadow: var(--card-glow-shadow);
        }

        .rating-stars {
            display: flex;
            gap: 0.2rem;
            margin-bottom: 1rem;
            color: var(--amber);
            font-size: 0.85rem;
        }
        .testimonial-quote {
            font-size: 0.92rem;
            color: var(--text-muted);
            line-height: 1.7;
            font-style: italic;
            margin-bottom: 1.5rem;
            flex: 1;
        }
        .author-box {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }
        .author-avatar {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--cyan));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 800;
            font-size: 0.85rem;
            flex-shrink: 0;
            box-shadow: 0 0 15px var(--primary-glow);
        }
        .author-info h4 {
            font-size: 0.9rem;
            font-weight: 700;
        }
        .author-info p {
            font-size: 0.78rem;
            color: var(--text-subtle);
        }

        .carousel-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }
        .carousel-btn {
            width: 42px; height: 42px;
            border-radius: 12px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition-smooth);
            font-size: 0.95rem;
        }
        .carousel-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 0 20px var(--primary-glow);
        }
        .carousel-dots {
            display: flex;
            gap: 0.5rem;
        }
        .carousel-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            background: var(--border-color);
            cursor: pointer;
            transition: var(--transition-smooth);
        }
        .carousel-dot.active {
            background: var(--primary);
            box-shadow: 0 0 10px var(--primary-glow);
            width: 28px;
            border-radius: 6px;
        }

        /* ══════════════════════ FAQ ACCORDION ══════════════════════ */
        .faq-section {
            padding: 7rem 0;
            position: relative;
            z-index: 2;
        }

        .faq-container {
            max-width: 780px;
            margin: 0 auto;
        }

        .faq-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            margin-bottom: 0.85rem;
            overflow: hidden;
            transition: var(--transition-smooth);
        }
        .faq-card:hover { border-color: var(--border-glow); }
        .faq-card.active {
            border-color: var(--primary);
            box-shadow: 0 0 30px rgba(99,102,241,0.1);
        }
        .faq-card.active .faq-indicator {
            background: var(--primary);
        }

        .faq-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            background: transparent;
            border: none;
            color: var(--text-main);
            font-size: 0.95rem;
            font-weight: 600;
            font-family: var(--font-sans);
            cursor: pointer;
            text-align: left;
            transition: var(--transition-smooth);
        }
        .faq-indicator {
            width: 4px;
            height: 24px;
            border-radius: 4px;
            background: var(--border-color);
            flex-shrink: 0;
            transition: var(--transition-smooth);
        }
        .faq-btn span { flex: 1; }
        .faq-chevron {
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            color: var(--text-subtle);
            font-size: 0.85rem;
        }
        .faq-card.active .faq-chevron { transform: rotate(180deg); color: var(--primary-light); }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s cubic-bezier(0.16, 1, 0.3, 1), padding 0.5s ease;
            padding: 0 1.5rem 0 3.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.75;
        }
        .faq-card.active .faq-answer {
            max-height: 300px;
            padding: 0 1.5rem 1.5rem 3.5rem;
        }

        /* ══════════════════════ CTA BANNER ══════════════════════ */
        .cta-section {
            padding: 7rem 0;
            position: relative;
            z-index: 2;
        }

        .cta-banner {
            background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(6,182,212,0.12));
            border: 1px solid var(--border-glow);
            border-radius: 28px;
            padding: 5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 70px -20px var(--primary-glow);
        }
        .cta-banner::before {
            content: '';
            position: absolute;
            top: -60%;
            left: -30%;
            width: 160%;
            height: 160%;
            background: radial-gradient(circle, rgba(99,102,241,0.18) 0%, transparent 55%);
            animation: ctaRotate 25s linear infinite;
            pointer-events: none;
        }
        @keyframes ctaRotate { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .cta-inner {
            position: relative;
            z-index: 2;
            max-width: 680px;
            margin: 0 auto;
        }
        .cta-inner h2 {
            font-size: clamp(2rem, 4vw, 3rem);
            margin-bottom: 1rem;
        }
        .cta-inner p {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            line-height: 1.7;
        }
        .cta-btns {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        /* ══════════════════════ FOOTER ══════════════════════ */
        footer {
            background: var(--bg-surface);
            border-top: 1px solid var(--border-color);
            padding: 5rem 0 2rem;
            position: relative;
            z-index: 2;
        }
        footer::before {
            content: '';
            position: absolute;
            top: 0; left: 10%; right: 10%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary), var(--cyan), transparent);
            border-radius: 2px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
            gap: 3rem;
            margin-bottom: 3.5rem;
        }

        .footer-brand p {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.75;
            margin: 1.25rem 0 1.5rem;
            max-width: 300px;
        }

        .system-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-family: var(--font-mono);
            color: var(--emerald);
            background: rgba(16,185,129,0.08);
            border: 1px solid rgba(16,185,129,0.2);
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
        }

        .footer-col h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 1.5rem;
        }
        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .footer-links a {
            font-size: 0.88rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition-smooth);
        }
        .footer-links a:hover {
            color: var(--primary-light);
            transform: translateX(4px);
        }
        .footer-links a i { font-size: 0.7rem; color: var(--text-subtle); }

        .footer-subscribe {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .footer-subscribe input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-main);
            font-family: var(--font-sans);
            font-size: 0.88rem;
            outline: none;
            transition: var(--transition-smooth);
        }
        .footer-subscribe input::placeholder { color: var(--text-subtle); }
        .footer-subscribe input:focus { border-color: var(--border-glow); box-shadow: 0 0 20px var(--primary-glow); }

        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }
        .footer-bottom p {
            font-size: 0.82rem;
            color: var(--text-subtle);
        }
        .footer-socials {
            display: flex;
            gap: 0.65rem;
        }
        .social-link {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 0.9rem;
            transition: var(--transition-smooth);
        }
        .social-link:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px var(--primary-glow);
        }

        /* ══════════════════════ COMPLAINT MODAL ══════════════════════ */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: var(--modal-overlay);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            opacity: 0;
            visibility: hidden;
            transition: all 0.35s ease;
        }
        .modal-overlay.open { opacity: 1; visibility: visible; }

        .modal-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 2.5rem;
            max-width: 640px;
            width: 100%;
            max-height: 85vh;
            overflow-y: auto;
            position: relative;
            transform: scale(0.9) translateY(20px);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        }
        .modal-overlay.open .modal-card { transform: scale(1) translateY(0); }

        .modal-close {
            position: absolute;
            top: 1.25rem; right: 1.25rem;
            width: 36px; height: 36px;
            border-radius: 10px;
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition-smooth);
            font-size: 1rem;
        }
        .modal-close:hover {
            background: var(--rose);
            border-color: var(--rose);
            color: #fff;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 0.4rem;
        }
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-main);
            font-family: var(--font-sans);
            font-size: 0.9rem;
            outline: none;
            transition: var(--transition-smooth);
        }
        .form-control:focus { border-color: var(--border-glow); box-shadow: 0 0 20px var(--primary-glow); }
        .form-control::placeholder { color: var(--text-subtle); }
        textarea.form-control { resize: vertical; }
        select.form-control { cursor: pointer; }

        .anon-toggle-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background: rgba(6, 182, 212, 0.06);
            border: 1px solid rgba(6, 182, 212, 0.15);
            border-radius: 14px;
            margin-bottom: 1.5rem;
        }
        .switch-toggle {
            position: relative;
            width: 48px; height: 26px;
            cursor: pointer;
        }
        .switch-toggle input { display: none; }
        .slider-round {
            position: absolute;
            inset: 0;
            background: var(--border-color);
            border-radius: 9999px;
            transition: var(--transition-smooth);
        }
        .slider-round::before {
            content: '';
            position: absolute;
            width: 20px; height: 20px;
            bottom: 3px; left: 3px;
            background: #fff;
            border-radius: 50%;
            transition: var(--transition-smooth);
        }
        .switch-toggle input:checked + .slider-round { background: var(--cyan); }
        .switch-toggle input:checked + .slider-round::before { transform: translateX(22px); }

        /* ══════════════════════ SCROLL REVEAL ANIMATIONS ══════════════════════ */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        .reveal-delay-4 { transition-delay: 0.4s; }
        .reveal-delay-5 { transition-delay: 0.5s; }

        /* ══════════════════════ RESPONSIVE ══════════════════════ */
        @media (max-width: 1200px) {
            .bento-grid { grid-template-columns: repeat(2, 1fr); }
            .bento-grid .bento-card:nth-child(1),
            .bento-grid .bento-card:nth-child(4) { grid-column: span 1; }
        }

        @media (max-width: 992px) {
            .hero-title { font-size: clamp(2.2rem, 5vw, 3.2rem); }
            .radar-stats-grid { grid-template-columns: repeat(2, 1fr); }
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .testimonial-inner-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            header {
                top: 10px;
                width: calc(100% - 24px);
                border-radius: 20px;
            }
            .nav-menu, .nav-actions .nav-cta, .nav-separator { display: none; }
            .mobile-toggle { display: flex; }
            .hero-section { padding: 8rem 0 4rem; min-height: auto; }
            .hero-stats-row { grid-template-columns: 1fr; max-width: 300px; }
            .bento-grid { grid-template-columns: 1fr; }
            .radar-stats-grid { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr; }
            .footer-bottom { flex-direction: column; text-align: center; gap: 1rem; }
            .orbit-container { display: none; }
            .timeline-wrapper { padding-left: 0; }

            /* Mobile Drawer Menu */
            .nav-menu.mobile-open {
                display: flex !important;
                flex-direction: column;
                position: absolute;
                top: calc(100% + 10px);
                left: 0;
                width: 100%;
                background: var(--bg-surface);
                border: 1px solid var(--border-color);
                border-radius: 20px;
                padding: 1rem;
                gap: 0.25rem;
                box-shadow:
                    0 20px 60px rgba(0,0,0,0.5),
                    0 0 0 1px rgba(99,102,241,0.1),
                    inset 0 1px 0 rgba(255,255,255,0.05);
                backdrop-filter: blur(20px);
                animation: mobileMenuSlide 0.3s cubic-bezier(0.16,1,0.3,1);
            }
            @keyframes mobileMenuSlide {
                from { opacity: 0; transform: translateY(-12px) scale(0.97); }
                to   { opacity: 1; transform: translateY(0) scale(1); }
            }
            .nav-menu.mobile-open .nav-link {
                padding: 0.75rem 1rem;
                border-radius: 12px;
                font-size: 0.95rem;
                font-weight: 500;
                color: var(--text-muted);
                border-bottom: 1px solid rgba(255,255,255,0.04);
            }
            .nav-menu.mobile-open .nav-link:last-child { border-bottom: none; }
            .nav-menu.mobile-open .nav-link.active {
                background: rgba(99,102,241,0.1);
                color: var(--primary-light);
            }
            .nav-menu.mobile-open .nav-link i {
                width: 18px;
                text-align: center;
                font-size: 0.85rem;
            }
            /* Show CTA inside mobile menu */
            .nav-menu.mobile-open::after {
                content: '';
                display: block;
                height: 0.5rem;
            }
            .mobile-cta-wrap {
                display: none;
                padding: 0.5rem 0 0.25rem;
            }
            .nav-menu.mobile-open + .nav-actions .mobile-cta-wrap {
                display: flex;
            }
        }

        @media (max-width: 480px) {
            .hero-title { font-size: 2rem; }
            .hero-description { font-size: 1rem; }
            .cta-banner { padding: 3rem 1.5rem; }
            .dashboard-glass { padding: 1.5rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Background Effects -->
    <div class="ambient-orb ambient-orb-1"></div>
    <div class="ambient-orb ambient-orb-2"></div>
    <div class="ambient-orb ambient-orb-3"></div>
    <div class="grid-overlay"></div>

    <!-- ══════════════════════ PREMIUM GLASSMORPHIC FLOATING NAVBAR ══════════════════════ -->
    <header id="siteHeader">
        <nav class="navbar">

            <!-- Brand Logo -->
            <a href="#" class="brand-logo" aria-label="CCMS Home">
                <div class="logo-icon-box">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span class="logo-shield-pulse"></span>
                </div>
                <div class="brand-text">
                    <h1>CCMS</h1>
                    <span class="brand-sub">Anti-Corruption Sentinel</span>
                </div>
            </a>

            <div class="nav-separator"></div>

            <!-- Navigation Links -->
            <ul class="nav-menu" id="navMenu">
                <li><a href="#hero"      class="nav-link active"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="#security"  class="nav-link"><i class="fa-solid fa-lock"></i> Security</a></li>
                <li><a href="#analytics" class="nav-link"><i class="fa-solid fa-chart-line"></i> Live Radar</a></li>
                <li><a href="#faq"       class="nav-link"><i class="fa-solid fa-circle-question"></i> FAQ</a></li>
            </ul>

            <!-- Right-side Actions -->
            <div class="nav-actions">
                <!-- Theme Switcher -->
                <div class="theme-switcher" role="group" aria-label="Theme selector">
                    <button class="theme-btn active" data-set-theme="dark"    title="Midnight Cyber">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                    <button class="theme-btn"        data-set-theme="light"   title="Executive Light">
                        <i class="fa-solid fa-sun"></i>
                    </button>
                    <button class="theme-btn"        data-set-theme="emerald" title="Emerald Guard">
                        <i class="fa-solid fa-leaf"></i>
                    </button>
                </div>

                <div class="nav-separator"></div>

                <!-- Sign In Link -->
                <a href="login.php" class="nav-link" style="padding: 0.5rem 0.8rem;" title="Access Portals">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Sign In</span>
                </a>

                <!-- Create Account / CTA Button -->
                <a href="register.php" class="nav-cta" id="navFileCta" title="Create New Account">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Create Account</span>
                </a>

                <!-- Mobile Toggle -->
                <button class="mobile-toggle" id="mobileMenuBtn" aria-label="Toggle navigation menu" aria-expanded="false">
                    <i class="fa-solid fa-bars" id="mobileMenuIcon"></i>
                </button>
            </div>
        </nav>
    </header>

    <!-- ══════════════════════ CINEMATIC HERO SECTION ══════════════════════ -->
    <section class="hero-section" id="hero">
        <div class="hero-bg-mesh"></div>
        <canvas id="particleCanvas"></canvas>

        <!-- Orbiting Icons -->
        <div class="orbit-container">
            <div class="orbit-ring">
                <div class="orbit-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="orbit-icon"><i class="fa-solid fa-lock"></i></div>
                <div class="orbit-icon"><i class="fa-solid fa-fingerprint"></i></div>
                <div class="orbit-icon"><i class="fa-solid fa-eye-slash"></i></div>
            </div>
        </div>

        <div class="container">
            <div class="hero-content-wrapper">
                <div class="badge-pill hero-badge">
                    <span class="live-dot"></span>
                    <span>Zero-Knowledge 256-Bit Encrypted Portal • v2.4 Live</span>
                </div>

                <h1 class="hero-title">
                    Eradicate Corruption with <span class="gradient-text">Absolute Confidentiality</span> & <span class="typewriter-word" id="typewriterText">Real-Time Audits</span>
                </h1>

                <p class="hero-description">
                    Empowering citizens, whistleblowers, and oversight agencies with an immutable complaint pipeline, cryptographic anonymity, and instant case forensics.
                </p>

                <div class="hero-cta-group">
                    <a href="citizen/file-complaint.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1rem;">
                        <i class="fa-solid fa-file-shield"></i> Lodge Confidential Complaint
                    </a>
                    <a href="citizen/track-complaint.php" class="btn btn-secondary" style="padding: 1rem 2rem; font-size: 1rem;">
                        <i class="fa-solid fa-satellite-dish"></i> Live Case Radar
                    </a>
                </div>

                <div class="hero-stats-row">
                    <div class="hero-stat-item">
                        <h3 class="counter" data-target="100">0</h3>
                        <p>% Whistleblower Anonymity</p>
                    </div>
                    <div class="hero-stat-item">
                        <h3 class="counter" data-target="98.4">0</h3>
                        <p>% SLA Resolution Rate</p>
                    </div>
                    <div class="hero-stat-item">
                        <h3 class="counter" data-target="1480">0</h3>
                        <p>Cases Investigated</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════ MARQUEE TICKER ══════════════════════ -->
    <div class="marquee-section">
        <div class="marquee-track">
            <div class="marquee-item"><i class="fa-solid fa-shield-halved"></i> 256-BIT QUANTUM-RESISTANT ENCRYPTION</div>
            <div class="marquee-item"><i class="fa-solid fa-user-ninja"></i> UNTRACEABLE WHISTLEBLOWER SHIELD</div>
            <div class="marquee-item"><i class="fa-solid fa-bolt"></i> REAL-TIME SLA ESCALATION ENGINE</div>
            <div class="marquee-item"><i class="fa-solid fa-fingerprint"></i> IMMUTABLE AUDIT LOGS & EVIDENCE VAULT</div>
            <div class="marquee-item"><i class="fa-solid fa-building-columns"></i> 18+ GOVERNMENT DEPARTMENTS</div>
            <div class="marquee-item"><i class="fa-solid fa-chart-pie"></i> AUTOMATED INTEGRITY REPORTS</div>
            <!-- Duplicated for seamless loop -->
            <div class="marquee-item"><i class="fa-solid fa-shield-halved"></i> 256-BIT QUANTUM-RESISTANT ENCRYPTION</div>
            <div class="marquee-item"><i class="fa-solid fa-user-ninja"></i> UNTRACEABLE WHISTLEBLOWER SHIELD</div>
            <div class="marquee-item"><i class="fa-solid fa-bolt"></i> REAL-TIME SLA ESCALATION ENGINE</div>
            <div class="marquee-item"><i class="fa-solid fa-fingerprint"></i> IMMUTABLE AUDIT LOGS & EVIDENCE VAULT</div>
            <div class="marquee-item"><i class="fa-solid fa-building-columns"></i> 18+ GOVERNMENT DEPARTMENTS</div>
            <div class="marquee-item"><i class="fa-solid fa-chart-pie"></i> AUTOMATED INTEGRITY REPORTS</div>
        </div>
    </div>

    <!-- ══════════════════════ BENTO GRID: SECURITY FEATURES ══════════════════════ -->
    <section class="security-section" id="security">
        <div class="container">
            <div class="section-header reveal">
                <div class="badge-pill"><i class="fa-solid fa-lock"></i> Architecture Pillars</div>
                <h2 class="section-title">Engineered for Absolute Integrity & Privacy</h2>
                <p class="section-subtitle">
                    State-of-the-art security mechanisms ensure that no identity is ever exposed while guaranteeing every piece of evidence is tamper-proof.
                </p>
            </div>

            <div class="bento-grid">
                <!-- Card 1 (wide) -->
                <div class="bento-card reveal reveal-delay-1">
                    <div class="glow-spot"></div>
                    <div class="bento-icon"><i class="fa-solid fa-user-secret"></i></div>
                    <h3>Zero-Knowledge Whistleblower Shield</h3>
                    <p>Personal IP addresses and device fingerprints are automatically stripped before reaching database nodes. Anonymity is mathematically guaranteed with zero-knowledge proof protocols.</p>
                    <div class="bento-tags">
                        <span class="bento-tag">No IP Logging</span>
                        <span class="bento-tag">Tor/VPN Compatible</span>
                        <span class="bento-tag">Burner Token Auth</span>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bento-card reveal reveal-delay-2">
                    <div class="glow-spot"></div>
                    <div class="bento-icon" style="background: linear-gradient(135deg, rgba(6,182,212,0.15), rgba(16,185,129,0.1));"><i class="fa-solid fa-vault" style="color: var(--cyan);"></i></div>
                    <h3>Encrypted Evidence Vault</h3>
                    <p>Documents, audio, and images are encrypted client-side using military-grade AES-256 before being stored in isolated cold storage.</p>
                    <div class="bento-tags">
                        <span class="bento-tag">Client-Side AES</span>
                        <span class="bento-tag">Malware Scrub</span>
                        <span class="bento-tag">Digital Watermarking</span>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bento-card reveal reveal-delay-3">
                    <div class="glow-spot"></div>
                    <div class="bento-icon" style="background: linear-gradient(135deg, rgba(139,92,246,0.15), rgba(99,102,241,0.1));"><i class="fa-solid fa-diagram-project" style="color: var(--violet);"></i></div>
                    <h3>Automated Department Triage</h3>
                    <p>Intelligent routing assigns complaints to the appropriate anti-corruption commissioner based on jurisdiction and severity tiers.</p>
                    <div class="bento-tags">
                        <span class="bento-tag">Smart Routing</span>
                        <span class="bento-tag">Conflict-of-Interest Checks</span>
                        <span class="bento-tag">SLA Watchdog</span>
                    </div>
                </div>

                <!-- Card 4 (wide) -->
                <div class="bento-card reveal reveal-delay-4">
                    <div class="glow-spot"></div>
                    <div class="bento-icon" style="background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(244,63,94,0.1));"><i class="fa-solid fa-clock-rotate-left" style="color: var(--amber);"></i></div>
                    <h3>Immutable Forensic Audit Trail</h3>
                    <p>Every officer action, comment, status change, and document access creates a cryptographically signed activity log that cannot be modified or deleted. Full chain-of-custody compliance for judicial proceedings.</p>
                    <div class="bento-tags">
                        <span class="bento-tag">Write-Once Logs</span>
                        <span class="bento-tag">Hash Chaining</span>
                        <span class="bento-tag">Tamper Alerts</span>
                    </div>
                </div>

                <!-- Card 5 -->
                <div class="bento-card reveal reveal-delay-5">
                    <div class="glow-spot"></div>
                    <div class="bento-icon" style="background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(6,182,212,0.1));"><i class="fa-solid fa-bell-slash" style="color: var(--emerald);"></i></div>
                    <h3>Encrypted Notification Channel</h3>
                    <p>Stay informed with encrypted status alerts and anonymous 2-way query channels without providing any contact details.</p>
                    <div class="bento-tags">
                        <span class="bento-tag">Anonymous Inbox</span>
                        <span class="bento-tag">Case Token Access</span>
                        <span class="bento-tag">End-to-End Messages</span>
                    </div>
                </div>

                <!-- Card 6 -->
                <div class="bento-card reveal reveal-delay-1">
                    <div class="glow-spot"></div>
                    <div class="bento-icon" style="background: linear-gradient(135deg, rgba(244,63,94,0.15), rgba(139,92,246,0.1));"><i class="fa-solid fa-file-circle-check" style="color: var(--rose);"></i></div>
                    <h3>Automated Legal & Audit Exports</h3>
                    <p>One-click compilation of certified investigation reports formatted to judicial evidentiary standards.</p>
                    <div class="bento-tags">
                        <span class="bento-tag">Signed PDF Dossiers</span>
                        <span class="bento-tag">Metadata Verifiers</span>
                        <span class="bento-tag">Judicial Ready</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════ ANIMATED PROCESS TIMELINE ══════════════════════ -->
    <section class="process-section" id="process">
        <div class="container">
            <div class="section-header reveal">
                <div class="badge-pill"><i class="fa-solid fa-route"></i> Transparent Workflow</div>
                <h2 class="section-title">From Submission to Justice in 4 Stages</h2>
                <p class="section-subtitle">
                    A streamlined, accountable path designed to eliminate delays and ensure thorough departmental oversight.
                </p>
            </div>

            <div class="timeline-wrapper">
                <div class="timeline-line"></div>
                <div class="timeline-line-fill" id="timelineLineFill"></div>

                <div class="timeline-step" data-step="1">
                    <div class="timeline-node">01</div>
                    <div class="timeline-step-content">
                        <h3>Encrypted Filing</h3>
                        <p>Citizen submits detailed incident report and evidence with choice of complete anonymity. All data is sealed with SHA-256 checksums.</p>
                    </div>
                </div>

                <div class="timeline-step" data-step="2">
                    <div class="timeline-node">02</div>
                    <div class="timeline-step-content">
                        <h3>Forensic Triage</h3>
                        <p>System validates evidence checksums, performs conflict-of-interest screening, and routes case to an independent investigation unit.</p>
                    </div>
                </div>

                <div class="timeline-step" data-step="3">
                    <div class="timeline-node">03</div>
                    <div class="timeline-step-content">
                        <h3>Active Probe</h3>
                        <p>Assigned officers gather depositions and execute on-site verification with strict SLA timers and automated escalation protocols.</p>
                    </div>
                </div>

                <div class="timeline-step" data-step="4">
                    <div class="timeline-node">04</div>
                    <div class="timeline-step-content">
                        <h3>Resolution & Action</h3>
                        <p>Final findings are published, disciplinary or legal action is logged, and citizen is notified via their encrypted anonymous channel.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════ LIVE ANALYTICS RADAR ══════════════════════ -->
    <section class="analytics-section" id="analytics">
        <div class="container">
            <div class="dashboard-glass reveal">
                <div class="radar-top-bar">
                    <div>
                        <div class="badge-pill" style="margin-bottom: 0.5rem;">
                            <span class="live-dot"></span> Live Anti-Corruption Telemetry
                        </div>
                        <h2>National Oversight Radar</h2>
                    </div>
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <span style="font-size: 0.82rem; color: var(--text-muted); font-family: var(--font-mono);">SYNC: LIVE (10s)</span>
                        <a href="admin/analytics.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.82rem;">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Full Analytics
                        </a>
                    </div>
                </div>

                <div class="radar-stats-grid">
                    <div class="radar-kpi">
                        <div class="radar-kpi-value counter" data-target="1482">0</div>
                        <span class="radar-kpi-tag growth-up">+14% MoM</span>
                        <div class="radar-kpi-label">Total Registered Complaints</div>
                    </div>
                    <div class="radar-kpi">
                        <div class="radar-kpi-value counter" data-target="89">0</div>
                        <span class="radar-kpi-tag growth-cyan">Active Now</span>
                        <div class="radar-kpi-label">Ongoing Investigations</div>
                    </div>
                    <div class="radar-kpi">
                        <div class="radar-kpi-value counter" data-target="1295">0</div>
                        <span class="radar-kpi-tag growth-up">87.4%</span>
                        <div class="radar-kpi-label">Successfully Resolved</div>
                    </div>
                    <div class="radar-kpi">
                        <div class="radar-kpi-value counter" data-target="4.2">0</div>
                        <span class="radar-kpi-tag growth-up">Days</span>
                        <div class="radar-kpi-label">Average Triage Time</div>
                    </div>
                </div>

                <div class="live-stream-box">
                    <div class="stream-header">
                        <h4><i class="fa-solid fa-satellite-dish" style="color: var(--cyan);"></i> Real-Time Public Activity Stream (Redacted)</h4>
                        <span class="badge-pill" style="font-size: 0.72rem; padding: 0.25rem 0.65rem;">Automated Dispatch</span>
                    </div>
                    <div id="streamList">
                        <div class="stream-row">
                            <div class="stream-left">
                                <span class="stream-badge">CASE #CCMS-9831</span>
                                <span>Evidence bundle securely uploaded & verified by Hash Checksum.</span>
                            </div>
                            <span class="stream-time">2 mins ago</span>
                        </div>
                        <div class="stream-row">
                            <div class="stream-left">
                                <span class="stream-badge" style="color: var(--emerald);">CASE #CCMS-9740</span>
                                <span>Investigation completed: Disciplinary notice issued to Public Works division.</span>
                            </div>
                            <span class="stream-time">18 mins ago</span>
                        </div>
                        <div class="stream-row">
                            <div class="stream-left">
                                <span class="stream-badge" style="color: var(--amber);">CASE #CCMS-9712</span>
                                <span>Triage escalated to High-Priority tier by Anti-Corruption Cell #02.</span>
                            </div>
                            <span class="stream-time">45 mins ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════ TESTIMONIAL CAROUSEL ══════════════════════ -->
    <section class="testimonials-section" id="testimonials">
        <div class="container">
            <div class="section-header reveal">
                <div class="badge-pill"><i class="fa-solid fa-star"></i> Verified Trust</div>
                <h2 class="section-title">Trusted by Investigators and Citizens</h2>
                <p class="section-subtitle">
                    Hear from individuals who experienced first-hand how CCMS transformed transparency and brought accountability.
                </p>
            </div>

            <div class="carousel-viewport reveal">
                <div class="carousel-track" id="carouselTrack">
                    <!-- Slide 1 -->
                    <div class="carousel-slide">
                        <div class="testimonial-inner-grid">
                            <div class="testimonial-card">
                                <div>
                                    <div class="rating-stars">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                                    </div>
                                    <p class="testimonial-quote">"The anonymous submission mechanism gave me the confidence to report bribery in procurement without risking my livelihood. Within 2 weeks, corrective action was taken."</p>
                                </div>
                                <div class="author-box">
                                    <div class="author-avatar">CW</div>
                                    <div class="author-info">
                                        <h4>Anonymous Whistleblower</h4>
                                        <p>Municipal Contractor</p>
                                    </div>
                                </div>
                            </div>

                            <div class="testimonial-card">
                                <div>
                                    <div class="rating-stars">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                                    </div>
                                    <p class="testimonial-quote">"As an anti-corruption commissioner, the automated SLA counters and tamper-proof audit trail have cut our case backlog by 60%. Essential public infrastructure."</p>
                                </div>
                                <div class="author-box">
                                    <div class="author-avatar">SR</div>
                                    <div class="author-info">
                                        <h4>Inspector S. Rahman</h4>
                                        <p>Special Oversight Bureau</p>
                                    </div>
                                </div>
                            </div>

                            <div class="testimonial-card">
                                <div>
                                    <div class="rating-stars">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                                    </div>
                                    <p class="testimonial-quote">"The transparent tracking timeline provided complete peace of mind. Knowing exactly when our village's school fund complaint was reviewed restored my faith in governance."</p>
                                </div>
                                <div class="author-box">
                                    <div class="author-avatar">TK</div>
                                    <div class="author-info">
                                        <h4>Tariq K.</h4>
                                        <p>Civic Rights Advocate</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 2 -->
                    <div class="carousel-slide">
                        <div class="testimonial-inner-grid">
                            <div class="testimonial-card">
                                <div>
                                    <div class="rating-stars">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                                    </div>
                                    <p class="testimonial-quote">"The evidence vault and digital watermarking ensured that our submitted documents could not be tampered with during the entire investigation process."</p>
                                </div>
                                <div class="author-box">
                                    <div class="author-avatar">AP</div>
                                    <div class="author-info">
                                        <h4>Amara P.</h4>
                                        <p>Government Auditor</p>
                                    </div>
                                </div>
                            </div>

                            <div class="testimonial-card">
                                <div>
                                    <div class="rating-stars">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                                    </div>
                                    <p class="testimonial-quote">"CCMS brought unprecedented accountability to our department. Officers now know every action is logged immutably — it has fundamentally changed behavior."</p>
                                </div>
                                <div class="author-box">
                                    <div class="author-avatar">DM</div>
                                    <div class="author-info">
                                        <h4>Director D. Mishra</h4>
                                        <p>Anti-Corruption Bureau</p>
                                    </div>
                                </div>
                            </div>

                            <div class="testimonial-card">
                                <div>
                                    <div class="rating-stars">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                                    </div>
                                    <p class="testimonial-quote">"Being able to file a complaint from a public terminal without leaving any trace was life-changing. The burner token system is brilliant."</p>
                                </div>
                                <div class="author-box">
                                    <div class="author-avatar">RN</div>
                                    <div class="author-info">
                                        <h4>Anonymous Citizen</h4>
                                        <p>Rural District Resident</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="carousel-controls">
                <button class="carousel-btn" id="carouselPrev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
                <div class="carousel-dots" id="carouselDots">
                    <div class="carousel-dot active" data-slide="0"></div>
                    <div class="carousel-dot" data-slide="1"></div>
                </div>
                <button class="carousel-btn" id="carouselNext" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <!-- ══════════════════════ FAQ ACCORDION ══════════════════════ -->
    <section class="faq-section" id="faq">
        <div class="container">
            <div class="section-header reveal">
                <div class="badge-pill"><i class="fa-solid fa-circle-question"></i> Clarifications</div>
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">
                    Common questions regarding your safety, legal standing, evidence handling, and investigation timelines.
                </p>
            </div>

            <div class="faq-container">
                <div class="faq-card active reveal reveal-delay-1">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <div class="faq-indicator"></div>
                        <span>Can anyone trace my identity if I file anonymously?</span>
                        <div class="faq-chevron"><i class="fa-solid fa-chevron-down"></i></div>
                    </button>
                    <div class="faq-answer">
                        No. When you submit an anonymous report, our system strips your IP address, device headers, and user-agent metadata before the record touches our encrypted database. You are assigned a unique cryptographic Case Token that serves as your sole key for tracking.
                    </div>
                </div>

                <div class="faq-card reveal reveal-delay-2">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <div class="faq-indicator"></div>
                        <span>What formats of evidence can I upload?</span>
                        <div class="faq-chevron"><i class="fa-solid fa-chevron-down"></i></div>
                    </button>
                    <div class="faq-answer">
                        You can attach PDF documents, spreadsheets, photographs (JPEG, PNG), audio recordings (MP3, WAV), and video files up to 100MB. All files are automatically sanitized to remove EXIF location metadata before forensic storage.
                    </div>
                </div>

                <div class="faq-card reveal reveal-delay-3">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <div class="faq-indicator"></div>
                        <span>How long does an investigation usually take?</span>
                        <div class="faq-chevron"><i class="fa-solid fa-chevron-down"></i></div>
                    </button>
                    <div class="faq-answer">
                        Initial triage and department routing are completed within 24 to 48 hours. Preliminary inquiries conclude within 14 business days according to national SLA statutory limits. You can monitor live progress on your tracking terminal.
                    </div>
                </div>

                <div class="faq-card reveal reveal-delay-4">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <div class="faq-indicator"></div>
                        <span>What happens after a corrupt act is confirmed?</span>
                        <div class="faq-chevron"><i class="fa-solid fa-chevron-down"></i></div>
                    </button>
                    <div class="faq-answer">
                        Validated dossiers are automatically forwarded to the appropriate disciplinary tribunal, vigilance commission, or judicial prosecutor. A public summary is appended to the audit ledger while preserving complainant confidentiality.
                    </div>
                </div>

                <div class="faq-card reveal reveal-delay-5">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <div class="faq-indicator"></div>
                        <span>Is there legal whistleblower protection?</span>
                        <div class="faq-chevron"><i class="fa-solid fa-chevron-down"></i></div>
                    </button>
                    <div class="faq-answer">
                        Yes. Submissions under CCMS fall under national Whistleblower Protection legislation, shielding complainants from employer retaliation, harassment, or unlawful termination.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════ CTA BANNER ══════════════════════ -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-banner reveal">
                <div class="cta-inner">
                    <div class="badge-pill" style="margin-bottom: 1rem;"><i class="fa-solid fa-bolt"></i> Break the Silence</div>
                    <h2>Take a Stand for Transparent Governance Today</h2>
                    <p>Your single report can halt systemic corruption and protect public resources. Safe, anonymous, and backed by law.</p>
                    <div class="cta-btns">
                        <button class="btn btn-primary" onclick="openComplaintModal()" style="font-size: 1.05rem; padding: 1rem 2.25rem;">
                            <i class="fa-solid fa-bullhorn"></i> File a Complaint Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════ FOOTER ══════════════════════ -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="#" class="brand-logo" style="margin-bottom: 0.5rem;">
                        <div class="logo-icon-box">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div class="brand-text">
                            <h1>CCMS</h1>
                            <span>Anti-Corruption Sentinel</span>
                        </div>
                    </a>
                    <p>The official high-security platform for reporting, investigating, and resolving corruption complaints with absolute confidentiality.</p>
                    <div class="system-status">
                        <span class="live-dot"></span> System Operational • 99.99% Uptime
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Navigation</h4>
                    <div class="footer-links">
                        <a href="#hero"><i class="fa-solid fa-angle-right"></i> Home</a>
                        <a href="#security"><i class="fa-solid fa-angle-right"></i> Security Matrix</a>
                        <a href="#process"><i class="fa-solid fa-angle-right"></i> Investigation Flow</a>
                        <a href="#analytics"><i class="fa-solid fa-angle-right"></i> National Radar</a>
                        <a href="#faq"><i class="fa-solid fa-angle-right"></i> Knowledge Base</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Access Portals</h4>
                    <div class="footer-links">
                        <a href="login.php"><i class="fa-solid fa-arrow-right-to-bracket"></i> Unified Portal Sign In</a>
                        <a href="register.php"><i class="fa-solid fa-user-plus"></i> Create New Account</a>
                        <a href="citizen/dashboard.php"><i class="fa-solid fa-users"></i> Citizen Dashboard</a>
                        <a href="officer/index.php"><i class="fa-solid fa-user-shield"></i> Officer Command</a>
                        <a href="admin/index.php"><i class="fa-solid fa-lock"></i> Admin Oversight</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Transparency Alerts</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                        Receive bi-weekly transparency reports & national oversight statistics.
                    </p>
                    <div class="footer-subscribe">
                        <input type="email" placeholder="Enter anonymous email..." id="newsletterEmail">
                        <button class="btn btn-primary" onclick="handleSubscribe()" style="padding: 0.7rem; width: 100%;">
                            <i class="fa-solid fa-paper-plane"></i> Subscribe
                        </button>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 Corruption Complaint Management System (CCMS). Cryptographically Secured & Audited.</p>
                <div class="footer-socials">
                    <a href="#" class="social-link" title="GitHub"><i class="fa-brands fa-github"></i></a>
                    <a href="#" class="social-link" title="Twitter/X"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#" class="social-link" title="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
                    <a href="#" class="social-link" title="Telegram"><i class="fa-brands fa-telegram"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ══════════════════════ COMPLAINT MODAL ══════════════════════ -->
    <div class="modal-overlay" id="complaintModal">
        <div class="modal-card">
            <button class="modal-close" onclick="closeComplaintModal()" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div style="margin-bottom: 1.5rem;">
                <span class="badge-pill" style="margin-bottom: 0.5rem;"><i class="fa-solid fa-shield-halved"></i> 256-Bit Encrypted Form</span>
                <h2 style="font-size: 1.6rem; margin-top: 0.5rem; font-family: var(--font-display);">File Corruption Complaint</h2>
                <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 0.3rem;">
                    All submissions are cryptographically hashed. You may choose complete anonymity below.
                </p>
            </div>

            <form id="complaintForm" onsubmit="handleComplaintSubmit(event)">
                <!-- Anonymity Toggle -->
                <div class="anon-toggle-card">
                    <div>
                        <strong style="display: block; font-size: 0.92rem; color: var(--text-main);">
                            <i class="fa-solid fa-user-ninja" style="color: var(--cyan);"></i> Anonymous Whistleblower Mode
                        </strong>
                        <span style="font-size: 0.78rem; color: var(--text-muted);">
                            Do not store my name, email, or IP address.
                        </span>
                    </div>
                    <label class="switch-toggle">
                        <input type="checkbox" id="anonToggle" checked onchange="toggleAnonFields(this.checked)">
                        <span class="slider-round"></span>
                    </label>
                </div>

                <!-- Personal Info (hidden when anonymous) -->
                <div id="personalInfoFields" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Full Legal Name</label>
                            <input type="text" class="form-control" placeholder="e.g. John Doe">
                        </div>
                        <div class="form-group">
                            <label>Contact Email</label>
                            <input type="email" class="form-control" placeholder="email@domain.com">
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Target Department / Agency *</label>
                        <select class="form-control" id="complaintDept">
                            <option value="">Select Department...</option>
                            <option value="revenue">Revenue & Tax Administration</option>
                            <option value="procurement">Public Works & Procurement</option>
                            <option value="police">Law Enforcement & Traffic</option>
                            <option value="health">Healthcare & Medical Supply</option>
                            <option value="education">Education & University Grants</option>
                            <option value="land">Land Records & Urban Planning</option>
                            <option value="other">Other Government Agency</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Incident Date Approx *</label>
                        <input type="date" class="form-control" id="complaintDate">
                    </div>
                </div>

                <div class="form-group">
                    <label>Complaint Subject / Summary *</label>
                    <input type="text" class="form-control" id="complaintSubject" placeholder="e.g. Unlawful bribery demand for building permit clearance">
                </div>

                <div class="form-group">
                    <label>Detailed Incident Description *</label>
                    <textarea class="form-control" id="complaintNarrative" rows="4" placeholder="Describe the sequence of events, officer titles, demanded amounts, and locations..."></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fa-solid fa-paperclip"></i> Attach Evidence (Documents, Images, Audio)</label>
                    <input type="file" class="form-control" multiple style="padding: 0.6rem;">
                    <span style="font-size: 0.72rem; color: var(--text-subtle); display: block; margin-top: 4px;">
                        Supported: PDF, JPG, PNG, MP3, MP4 up to 50MB. EXIF data auto-sanitized.
                    </span>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeComplaintModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-lock"></i> Submit Encrypted Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════ JAVASCRIPT ══════════════════════ -->
    <script>
        /* ═══ Theme Switching ═══ */
        const themeButtons = document.querySelectorAll('[data-set-theme]');
        const htmlEl = document.documentElement;
        const savedTheme = localStorage.getItem('ccms_theme') || 'dark';
        setTheme(savedTheme);

        themeButtons.forEach(btn => {
            btn.addEventListener('click', () => setTheme(btn.getAttribute('data-set-theme')));
        });

        function setTheme(theme) {
            htmlEl.setAttribute('data-theme', theme);
            localStorage.setItem('ccms_theme', theme);
            themeButtons.forEach(btn => {
                btn.classList.toggle('active', btn.getAttribute('data-set-theme') === theme);
            });
        }

        /* ═══ Typewriter Effect ═══ */
        const phrases = ["Real-Time Audits", "Whistleblower Shields", "Immutable Records", "Zero-Tolerance Oversight"];
        let phraseIdx = 0, charIdx = 0, isDeleting = false;
        const typewriterEl = document.getElementById('typewriterText');

        function typeLoop() {
            const current = phrases[phraseIdx];
            typewriterEl.textContent = current.substring(0, isDeleting ? --charIdx : ++charIdx);
            let speed = isDeleting ? 35 : 85;
            if (!isDeleting && charIdx === current.length) { speed = 2200; isDeleting = true; }
            else if (isDeleting && charIdx === 0) { isDeleting = false; phraseIdx = (phraseIdx + 1) % phrases.length; speed = 400; }
            setTimeout(typeLoop, speed);
        }
        typeLoop();

        /* ═══ Header: Always Visible, Compact on Scroll ═══ */
        const header = document.getElementById('siteHeader');
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 50);
        }, { passive: true });

        /* ═══ Scrollspy: Active Nav Link ═══ */
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');
        const scrollspyObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    navLinks.forEach(link => {
                        link.classList.toggle('active', link.getAttribute('href') === '#' + entry.target.id);
                    });
                }
            });
        }, { threshold: 0.3, rootMargin: '-100px 0px -50% 0px' });
        sections.forEach(s => scrollspyObserver.observe(s));

        /* ═══ Animated Counters ═══ */
        const counterEls = document.querySelectorAll('.counter');
        let countersTriggered = false;
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !countersTriggered) {
                    countersTriggered = true;
                    counterEls.forEach(el => {
                        const target = parseFloat(el.dataset.target);
                        const isDecimal = target % 1 !== 0;
                        const duration = 2000, stepTime = 20;
                        const steps = duration / stepTime;
                        const increment = target / steps;
                        let current = 0;
                        const timer = setInterval(() => {
                            current += increment;
                            if (current >= target) {
                                el.textContent = isDecimal ? target.toFixed(1) : Math.round(target);
                                clearInterval(timer);
                            } else {
                                el.textContent = isDecimal ? current.toFixed(1) : Math.floor(current);
                            }
                        }, stepTime);
                    });
                }
            });
        }, { threshold: 0.3 });
        counterEls.forEach(c => counterObserver.observe(c));

        /* ═══ Scroll Reveal ═══ */
        const revealEls = document.querySelectorAll('.reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -50px 0px' });
        revealEls.forEach(el => revealObserver.observe(el));

        /* ═══ Timeline Scroll Animation ═══ */
        const timelineSteps = document.querySelectorAll('.timeline-step');
        const timelineFill = document.getElementById('timelineLineFill');
        const timelineObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    updateTimelineFill();
                }
            });
        }, { threshold: 0.5 });
        timelineSteps.forEach(s => timelineObserver.observe(s));

        function updateTimelineFill() {
            const visible = document.querySelectorAll('.timeline-step.visible').length;
            const total = timelineSteps.length;
            if (timelineFill) {
                timelineFill.style.height = (visible / total * 100) + '%';
            }
        }

        /* ═══ Bento Card Mouse Glow Effect ═══ */
        document.querySelectorAll('.bento-card').forEach(card => {
            const glow = card.querySelector('.glow-spot');
            if (!glow) return;
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                glow.style.left = (e.clientX - rect.left) + 'px';
                glow.style.top = (e.clientY - rect.top) + 'px';
            });
        });

        /* ═══ Testimonial Carousel ═══ */
        const track = document.getElementById('carouselTrack');
        const dots = document.querySelectorAll('.carousel-dot');
        let currentSlide = 0;
        const totalSlides = document.querySelectorAll('.carousel-slide').length;
        let carouselInterval;

        function goToSlide(idx) {
            currentSlide = idx;
            track.style.transform = `translateX(-${idx * 100}%)`;
            dots.forEach((d, i) => d.classList.toggle('active', i === idx));
        }

        document.getElementById('carouselPrev').addEventListener('click', () => {
            goToSlide((currentSlide - 1 + totalSlides) % totalSlides);
            resetCarouselTimer();
        });
        document.getElementById('carouselNext').addEventListener('click', () => {
            goToSlide((currentSlide + 1) % totalSlides);
            resetCarouselTimer();
        });
        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                goToSlide(parseInt(dot.dataset.slide));
                resetCarouselTimer();
            });
        });

        function startCarouselTimer() {
            carouselInterval = setInterval(() => goToSlide((currentSlide + 1) % totalSlides), 6000);
        }
        function resetCarouselTimer() { clearInterval(carouselInterval); startCarouselTimer(); }
        startCarouselTimer();

        // Pause on hover
        const viewport = document.querySelector('.carousel-viewport');
        viewport.addEventListener('mouseenter', () => clearInterval(carouselInterval));
        viewport.addEventListener('mouseleave', startCarouselTimer);

        /* ═══ FAQ Accordion ═══ */
        function toggleFaq(btn) {
            const card = btn.closest('.faq-card');
            const isActive = card.classList.contains('active');
            document.querySelectorAll('.faq-card').forEach(c => c.classList.remove('active'));
            if (!isActive) card.classList.add('active');
        }

        /* ═══ Complaint Modal ═══ */
        const modal = document.getElementById('complaintModal');
        function openComplaintModal() { modal.classList.add('open'); document.body.style.overflow = 'hidden'; }
        function closeComplaintModal() { modal.classList.remove('open'); document.body.style.overflow = 'auto'; }
        modal.addEventListener('click', (e) => { if (e.target === modal) closeComplaintModal(); });

        function toggleAnonFields(isAnon) {
            document.getElementById('personalInfoFields').style.display = isAnon ? 'none' : 'block';
        }

        function showInlineErrorIndex(id, msg) {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.borderColor = '#ef4444';
            let parent = el.closest('.form-group') || el.parentElement;
            let err = parent.querySelector('.inline-error-text');
            if (!err) {
                err = document.createElement('div');
                err.className = 'inline-error-text';
                err.style.color = '#ef4444';
                err.style.fontSize = '0.78rem';
                err.style.marginTop = '4px';
                err.style.fontWeight = '600';
                err.style.display = 'flex';
                err.style.alignItems = 'center';
                err.style.gap = '4px';
                parent.appendChild(err);
            }
            err.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${msg}`;
            err.style.display = 'flex';
        }

        function clearInlineErrorIndex(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.borderColor = '';
            let parent = el.closest('.form-group') || el.parentElement;
            let err = parent.querySelector('.inline-error-text');
            if (err) err.style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            ['complaintDept', 'complaintDate', 'complaintSubject', 'complaintNarrative'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', () => clearInlineErrorIndex(id));
                    el.addEventListener('change', () => clearInlineErrorIndex(id));
                }
            });
        });

        function handleComplaintSubmit(e) {
            e.preventDefault();
            let isValid = true;
            ['complaintDept', 'complaintDate', 'complaintSubject', 'complaintNarrative'].forEach(clearInlineErrorIndex);

            const dept = document.getElementById('complaintDept').value;
            const date = document.getElementById('complaintDate').value;
            const subject = document.getElementById('complaintSubject').value.trim();
            const narrative = document.getElementById('complaintNarrative').value.trim();

            if (!dept) {
                showInlineErrorIndex('complaintDept', 'Please select a target department.');
                isValid = false;
            }
            if (!date) {
                showInlineErrorIndex('complaintDate', 'Please select the incident date.');
                isValid = false;
            }
            if (!subject) {
                showInlineErrorIndex('complaintSubject', 'Please enter a complaint subject / summary.');
                isValid = false;
            }
            if (!narrative) {
                showInlineErrorIndex('complaintNarrative', 'Please enter detailed incident description.');
                isValid = false;
            }

            if (!isValid) return false;

            const token = 'CCMS-2026-' + Math.floor(1000 + Math.random() * 9000);
            alert(`🔒 Complaint Encrypted & Filed!\n\nYour Tracking Token: ${token}\n\nCopy this token to track your case anonymously.`);
            closeComplaintModal();
        }

        function handleSubscribe() {
            const input = document.getElementById('newsletterEmail');
            if (!input.value.includes('@')) { alert('Please enter a valid email.'); return; }
            alert('🎉 Subscribed to CCMS transparency alerts!');
            input.value = '';
        }

        /* ═══ Mobile Menu ═══ */
        const mobileMenuBtn  = document.getElementById('mobileMenuBtn');
        const mobileMenuIcon = document.getElementById('mobileMenuIcon');
        const navMenu        = document.getElementById('navMenu');

        mobileMenuBtn.addEventListener('click', () => {
            const isOpen = navMenu.classList.toggle('mobile-open');
            mobileMenuBtn.setAttribute('aria-expanded', isOpen);
            mobileMenuIcon.className = isOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        });

        // Close mobile menu when a nav link is clicked
        navMenu.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('mobile-open');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
                mobileMenuIcon.className = 'fa-solid fa-bars';
            });
        });

        /* ═══ Button Ripple Effect ═══ */
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const circle = document.createElement('span');
                circle.classList.add('ripple-circle');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                circle.style.width = circle.style.height = size + 'px';
                circle.style.left = (e.clientX - rect.left - size / 2) + 'px';
                circle.style.top = (e.clientY - rect.top - size / 2) + 'px';
                this.appendChild(circle);
                setTimeout(() => circle.remove(), 600);
            });
        });

        /* ═══ Interactive Particle Canvas ═══ */
        const canvas = document.getElementById('particleCanvas');
        const ctx = canvas.getContext('2d');
        let particles = [];
        let mouseX = 0, mouseY = 0;

        function resizeCanvas() {
            canvas.width = canvas.parentElement.offsetWidth;
            canvas.height = canvas.parentElement.offsetHeight;
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        canvas.parentElement.addEventListener('mousemove', (e) => {
            const rect = canvas.parentElement.getBoundingClientRect();
            mouseX = e.clientX - rect.left;
            mouseY = e.clientY - rect.top;
        });

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.vx = (Math.random() - 0.5) * 0.6;
                this.vy = (Math.random() - 0.5) * 0.6;
                this.radius = Math.random() * 2 + 0.5;
                this.baseAlpha = Math.random() * 0.3 + 0.2;
            }
            update() {
                // Subtle mouse attraction
                const dx = mouseX - this.x;
                const dy = mouseY - this.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 200 && dist > 0) {
                    this.vx += (dx / dist) * 0.02;
                    this.vy += (dy / dist) * 0.02;
                }
                this.vx *= 0.99;
                this.vy *= 0.99;
                this.x += this.vx;
                this.y += this.vy;
                if (this.x < 0 || this.x > canvas.width) this.vx *= -1;
                if (this.y < 0 || this.y > canvas.height) this.vy *= -1;
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(99, 102, 241, ${this.baseAlpha})`;
                ctx.fill();
            }
        }

        function initParticles() {
            particles = [];
            const count = Math.min(Math.floor(canvas.width / 20), 60);
            for (let i = 0; i < count; i++) particles.push(new Particle());
        }
        initParticles();

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
                particles[i].draw();
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 130) {
                        ctx.beginPath();
                        ctx.strokeStyle = `rgba(6, 182, 212, ${0.2 * (1 - dist / 130)})`;
                        ctx.lineWidth = 0.7;
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.stroke();
                    }
                }
            }
            requestAnimationFrame(animateParticles);
        }
        animateParticles();
    </script>
</body>
</html>
