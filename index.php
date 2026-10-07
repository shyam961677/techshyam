<?php
require_once __DIR__ . '/rate_limit.php';
require_once __DIR__ . '/admin/includes/auth.php';
rateLimit('index', 60, 60);
trackVisitor();

$profile  = readJson('profile.json') ?? [];
$skills   = readJson('skills.json')  ?? [];
$services = array_values(array_filter(readJson('services.json') ?? [], fn($s) => !empty($s['active'])));
$projects = array_values(array_filter(readJson('projects.json') ?? [], fn($p) => !empty($p['active'])));

usort($skills,   fn($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));
usort($services, fn($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));
usort($projects, fn($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

$seo      = $profile['seo']        ?? [];
$exp      = $profile['experience'] ?? [];
$edu      = $profile['education']  ?? [];
$social   = $profile['social']     ?? [];

$name     = htmlspecialchars($profile['name']     ?? 'Shyam Milan Yadav', ENT_QUOTES);
$title    = htmlspecialchars($profile['title']    ?? 'Senior Software Developer', ENT_QUOTES);
$subtitle = htmlspecialchars($profile['subtitle'] ?? 'PHP | Laravel | CodeIgniter', ENT_QUOTES);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($seo['meta_title'] ?? "$name | $title") ?></title>
  <meta name="description" content="<?= e($seo['meta_description'] ?? '') ?>">
  <meta name="keywords" content="<?= e($seo['meta_keywords']    ?? '') ?>">
  <meta property="og:title" content="<?= e($seo['meta_title'] ?? '') ?>">
  <meta property="og:description" content="<?= e($seo['meta_description'] ?? '') ?>">
  <link rel="shortcut icon" href="assets/images/fav.png" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <style>
    /* ════════════════════════════════════════════════════
   TOKENS
════════════════════════════════════════════════════ */
    :root {
      --p: #6366f1;
      --p-lt: #818cf8;
      --p-dk: #4f46e5;
      --ac: #06b6d4;
      --ac2: #f59e0b;
      --ok: #22c55e;
      --bg: #05050d;
      --bg2: #0c0c18;
      --bg3: #111122;
      --card: #0f0f1e;
      --card2: #16162a;
      --bdr: rgba(99, 102, 241, .15);
      --bdr2: rgba(255, 255, 255, .06);
      --txt: #e2e8f0;
      --txt2: #94a3b8;
      --txt3: #64748b;
      --white: #fff;
      --nav: 72px;
      --r: 14px;
      --tr: .32s cubic-bezier(.4, 0, .2, 1);
      --font: 'Inter', sans-serif;
      --mono: 'JetBrains Mono', monospace;
    }

    /* ════════════════════════════════════════════════════
   RESET
════════════════════════════════════════════════════ */
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    html {
      scroll-behavior: smooth;
      font-size: 16px
    }

    body {
      font-family: var(--font);
      background: var(--bg);
      color: var(--txt);
      overflow-x: hidden;
      line-height: 1.7;
      -webkit-font-smoothing: antialiased
    }

    img {
      max-width: 100%;
      display: block
    }

    a {
      text-decoration: none;
      color: inherit
    }

    ul {
      list-style: none
    }

    ::selection {
      background: var(--p);
      color: #fff
    }

    ::-webkit-scrollbar {
      width: 4px
    }

    ::-webkit-scrollbar-track {
      background: var(--bg2)
    }

    ::-webkit-scrollbar-thumb {
      background: var(--p);
      border-radius: 2px
    }

    /* ════════════════════════════════════════════════════
   CANVAS
════════════════════════════════════════════════════ */
    #bgCanvas {
      position: fixed;
      inset: 0;
      z-index: 0;
      pointer-events: none;
      opacity: .55
    }

    /* ════════════════════════════════════════════════════
   PRELOADER
════════════════════════════════════════════════════ */
    #pre {
      position: fixed;
      inset: 0;
      z-index: 9999;
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: opacity .5s, visibility .5s
    }

    #pre.hide {
      opacity: 0;
      visibility: hidden
    }

    .pre-ring {
      width: 48px;
      height: 48px;
      border: 3px solid rgba(99, 102, 241, .2);
      border-top-color: var(--p);
      border-radius: 50%;
      animation: spin .75s linear infinite
    }

    @keyframes spin {
      to {
        transform: rotate(360deg)
      }
    }

    /* ════════════════════════════════════════════════════
   SIDE NAV
════════════════════════════════════════════════════ */
    .sidenav {
      position: fixed;
      left: 0;
      top: 0;
      bottom: 0;
      width: var(--nav);
      background: linear-gradient(180deg, rgba(12, 12, 27, .98), rgba(5, 5, 13, .96));
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border-right: 1px solid rgba(145, 155, 255, .13);
      box-shadow: 8px 0 32px rgba(0, 0, 0, .12);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      padding: 22px 0;
      z-index: 500;
    }

    .sn-logo {
      width: 44px;
      height: 44px;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--p), var(--ac));
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1rem;
      color: #fff;
      flex-shrink: 0;
      letter-spacing: -.04em;
      border: 1px solid rgba(255, 255, 255, .22);
      box-shadow: 0 8px 24px rgba(99, 102, 241, .28), inset 0 1px rgba(255, 255, 255, .2);
      transition: transform .2s, box-shadow .2s;
    }

    .sn-logo:hover {
      transform: translateY(-2px);
      box-shadow: 0 11px 28px rgba(99, 102, 241, .38), inset 0 1px rgba(255, 255, 255, .2)
    }

    .sn-links {
      display: flex;
      flex-direction: column;
      gap: 7px;
      align-items: center
    }

    .sn-item {
      position: relative;
      width: 46px;
      height: 46px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #71809b;
      font-size: 1.2rem;
      transition: all var(--tr);
      cursor: pointer;
      border: 1px solid transparent;
    }

    .sn-item:hover,
    .sn-item.active {
      color: #b9c4ff;
      background: linear-gradient(135deg, rgba(99, 102, 241, .17), rgba(6, 182, 212, .08));
      border-color: rgba(145, 155, 255, .2);
      transform: translateY(-1px)
    }

    .sn-item.active {
      color: #a5b4fc;
      box-shadow: inset 0 0 18px rgba(99, 102, 241, .08)
    }

    .sn-item.active::before {
      position: absolute;
      left: -14px;
      width: 3px;
      height: 22px;
      border-radius: 0 4px 4px 0;
      background: linear-gradient(180deg, #a5b4fc, #22d3ee);
      box-shadow: 0 0 12px rgba(99, 102, 241, .55);
      content: ''
    }

    .sn-tip {
      position: absolute;
      left: calc(var(--nav) - 4px);
      background: #17172a;
      color: #edf0ff;
      padding: 7px 11px;
      border-radius: 9px;
      font-size: .7rem;
      font-weight: 600;
      white-space: nowrap;
      border: 1px solid rgba(145, 155, 255, .2);
      box-shadow: 0 8px 24px rgba(0, 0, 0, .35);
      pointer-events: none;
      z-index: 5;
      opacity: 0;
      transform: translateX(-8px);
      transition: all .18s;
    }

    .sn-item:hover .sn-tip,
    .sn-item:focus-visible .sn-tip {
      opacity: 1;
      transform: translateX(0)
    }

    .sn-social {
      display: flex;
      flex-direction: column;
      gap: 6px;
      align-items: center
    }

    .sn-social::before {
      width: 28px;
      height: 1px;
      margin: 0 0 7px;
      content: '';
      background: rgba(145, 155, 255, .18)
    }

    .sn-soc {
      width: 36px;
      height: 36px;
      border-radius: 11px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #71809b;
      font-size: .92rem;
      transition: all var(--tr);
      border: 1px solid transparent;
    }

    .sn-soc:hover {
      color: #b9c4ff;
      border-color: rgba(145, 155, 255, .2);
      background: rgba(99, 102, 241, .12);
      transform: translateY(-2px)
    }

    .sn-item:focus-visible,
    .sn-soc:focus-visible,
    .sn-logo:focus-visible {
      outline: 2px solid #8b9cff;
      outline-offset: 3px
    }

    /* ════════════════════════════════════════════════════
   MOBILE TOPBAR
════════════════════════════════════════════════════ */
    .topbar {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 58px;
      z-index: 500;
      background: rgba(5, 5, 13, .95);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border-bottom: 1px solid var(--bdr);
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
    }

    .topbar-brand {
      font-weight: 800;
      font-size: 1.05rem;
      color: var(--p-lt);
      letter-spacing: -.3px
    }

    .ham {
      width: 38px;
      height: 38px;
      border-radius: 9px;
      background: rgba(99, 102, 241, .1);
      border: 1px solid var(--bdr);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--txt);
      font-size: 1.25rem;
      cursor: pointer;
    }

    .mob-menu {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 490;
      background: rgba(5, 5, 13, .98);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding-top: 58px;
    }

    .mob-menu.open {
      display: flex
    }

    .mob-item {
      width: min(340px, 80%);
      padding: 14px 22px;
      border-radius: 11px;
      border: 1px solid var(--bdr);
      font-size: .88rem;
      font-weight: 600;
      color: var(--txt2);
      display: flex;
      align-items: center;
      gap: 12px;
      transition: all .2s;
    }

    .mob-item:hover,
    .mob-item.active {
      background: rgba(99, 102, 241, .12);
      color: var(--p-lt);
      border-color: var(--p)
    }

    /* ════════════════════════════════════════════════════
   PAGE WRAP
════════════════════════════════════════════════════ */
    .pw {
      margin-left: var(--nav);
      position: relative;
      z-index: 1
    }

    /* ════════════════════════════════════════════════════
   SECTION BASE
════════════════════════════════════════════════════ */
    section {
      padding: 96px 64px;
      position: relative
    }

    .sec-tag {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: rgba(99, 102, 241, .1);
      border: 1px solid rgba(99, 102, 241, .25);
      color: var(--p-lt);
      padding: 5px 14px;
      border-radius: 50px;
      font-size: .7rem;
      font-weight: 700;
      letter-spacing: .09em;
      text-transform: uppercase;
      margin-bottom: 14px;
    }

    .sec-h {
      font-size: clamp(1.7rem, 2.8vw, 2.4rem);
      font-weight: 800;
      color: var(--white);
      line-height: 1.2;
      margin-bottom: 10px
    }

    .sec-h span {
      background: linear-gradient(135deg, var(--p-lt), var(--ac));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text
    }

    .sec-sub {
      font-size: .9rem;
      color: var(--txt2);
      max-width: 540px;
      line-height: 1.8
    }

    /* ── Reveal ── */
    .rv {
      opacity: 0;
      transform: translateY(36px);
      transition: opacity .65s ease, transform .65s ease
    }

    .rv.in {
      opacity: 1;
      transform: translateY(0)
    }

    .rvl {
      opacity: 0;
      transform: translateX(-36px);
      transition: opacity .65s ease, transform .65s ease
    }

    .rvl.in {
      opacity: 1;
      transform: translateX(0)
    }

    .rvr {
      opacity: 0;
      transform: translateX(36px);
      transition: opacity .65s ease, transform .65s ease
    }

    .rvr.in {
      opacity: 1;
      transform: translateX(0)
    }

    .d1 {
      transition-delay: .08s
    }

    .d2 {
      transition-delay: .16s
    }

    .d3 {
      transition-delay: .24s
    }

    .d4 {
      transition-delay: .32s
    }

    .d5 {
      transition-delay: .4s
    }

    .d6 {
      transition-delay: .48s
    }

    /* ════════════════════════════════════════════════════
   HERO
════════════════════════════════════════════════════ */
    #home {
      min-height: 100vh;
      display: flex;
      align-items: center;
      padding: 0 72px;
      position: relative;
      overflow: hidden;
    }

    /* layered glow orbs */
    #home::before,
    #home::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      filter: blur(100px);
    }

    #home::before {
      width: 700px;
      height: 700px;
      background: radial-gradient(circle, rgba(99, 102, 241, .13) 0%, transparent 70%);
      top: -180px;
      left: -120px;
      animation: orbFloat 12s ease-in-out infinite;
    }

    #home::after {
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(6, 182, 212, .1) 0%, transparent 70%);
      bottom: -100px;
      right: 5%;
      animation: orbFloat 16s ease-in-out infinite reverse;
    }

    @keyframes orbFloat {

      0%,
      100% {
        transform: translate(0, 0) scale(1)
      }

      33% {
        transform: translate(40px, -30px) scale(1.05)
      }

      66% {
        transform: translate(-20px, 25px) scale(.97)
      }
    }

    /* grid lines overlay */
    #home .hero-grid-lines {
      position: absolute;
      inset: 0;
      pointer-events: none;
      z-index: 0;
      background-image:
        linear-gradient(rgba(99, 102, 241, .04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(99, 102, 241, .04) 1px, transparent 1px);
      background-size: 60px 60px;
      mask-image: radial-gradient(ellipse 80% 70% at 50% 50%, #000 30%, transparent 100%);
      -webkit-mask-image: radial-gradient(ellipse 80% 70% at 50% 50%, #000 30%, transparent 100%);
    }

    .hero-inner {
      max-width: 1500px;
      width: 100%;
      display: grid;
      grid-template-columns: minmax(0, 1.08fr) minmax(320px, .92fr);
      align-items: center;
      gap: clamp(38px, 5vw, 88px);
      position: relative;
      z-index: 1;
    }

    .hero-copy {
      min-width: 0
    }

    .hero-visual {
      position: relative;
      display: grid;
      min-height: 470px;
      place-items: center;
      isolation: isolate
    }

    .hero-visual::before {
      position: absolute;
      inset: 8% 5%;
      z-index: -2;
      border-radius: 50%;
      background: radial-gradient(ellipse, rgba(99, 102, 241, .18), rgba(6, 182, 212, .07) 42%, transparent 72%);
      filter: blur(22px);
      content: ''
    }

    .visual-orbit {
      position: absolute;
      width: min(82%, 420px);
      aspect-ratio: 1;
      border: 1px solid rgba(129, 140, 248, .13);
      border-radius: 50%;
      transform: rotate(-18deg)
    }

    .visual-orbit::before,
    .visual-orbit::after {
      position: absolute;
      inset: 8%;
      border: 1px solid rgba(34, 211, 238, .12);
      border-radius: 50%;
      content: ''
    }

    .visual-orbit::after {
      inset: 19%;
      border-color: rgba(129, 140, 248, .13)
    }

    .visual-card {
      position: relative;
      width: min(100%, 440px);
      padding: 0 22px 20px;
      border: 1px solid rgba(145, 155, 255, .2);
      border-radius: 18px;
      background: linear-gradient(145deg, rgba(20, 22, 43, .94), rgba(9, 11, 24, .96));
      box-shadow: 0 28px 80px rgba(0, 0, 0, .4), 0 0 50px rgba(99, 102, 241, .12);
      backdrop-filter: blur(16px);
      transform: rotate(2deg)
    }

    .visual-card-top {
      height: 54px;
      display: flex;
      align-items: center;
      gap: 7px;
      border-bottom: 1px solid rgba(145, 155, 255, .12);
      color: var(--txt3);
      font: 600 .7rem var(--mono)
    }

    .visual-card-top .window-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #fb7185
    }

    .visual-card-top .window-dot:nth-child(2) {
      background: #fbbf24
    }

    .visual-card-top .window-dot:nth-child(3) {
      background: #34d399
    }

    .visual-file {
      margin-left: 9px
    }

    .visual-code {
      display: grid;
      grid-template-columns: 24px 1fr;
      gap: 14px;
      padding: 25px 0 21px;
      font: 500 clamp(.7rem, 1vw, .84rem)/2 var(--mono)
    }

    .code-lines {
      color: #535c78;
      text-align: right;
      user-select: none
    }

    .code-content {
      color: #c5cee2;
      white-space: nowrap
    }

    .code-muted {
      color: #76829f
    }

    .code-purple {
      color: #c4a7ff
    }

    .code-cyan {
      color: #67e8f9
    }

    .code-green {
      color: #86efac
    }

    .code-yellow {
      color: #fcd34d
    }

    .visual-card-foot {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding-top: 15px;
      border-top: 1px solid rgba(145, 155, 255, .12);
      color: #97a4c0;
      font-size: .68rem
    }

    .visual-status {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      color: #86efac
    }

    .visual-status i {
      font-size: .55rem
    }

    .visual-float {
      position: absolute;
      display: flex;
      align-items: center;
      gap: 9px;
      padding: 11px 15px;
      border: 1px solid rgba(145, 155, 255, .2);
      border-radius: 12px;
      background: rgba(17, 19, 39, .92);
      box-shadow: 0 12px 32px rgba(0, 0, 0, .3);
      color: #dce5fa;
      font: 600 .7rem var(--mono);
      backdrop-filter: blur(10px);
      animation: floatCard 5s ease-in-out infinite
    }

    .visual-float i {
      color: #67e8f9;
      font-size: 1rem
    }

    .visual-float-top {
      top: 13%;
      right: 0
    }

    .visual-float-bottom {
      bottom: 12%;
      left: 0;
      animation-delay: -2.5s
    }

    .visual-float-bottom i {
      color: #a5b4fc
    }

    @keyframes floatCard {

      0%,
      100% {
        transform: translateY(0)
      }

      50% {
        transform: translateY(-8px)
      }
    }

    /* availability badge */
    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: rgba(34, 197, 94, .07);
      border: 1px solid rgba(34, 197, 94, .22);
      color: #86efac;
      padding: 7px 18px 7px 10px;
      border-radius: 50px;
      font-size: .7rem;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      margin-bottom: 28px;
      animation: badgePop .6s cubic-bezier(.34, 1.56, .64, 1) both;
    }

    @keyframes badgePop {
      from {
        opacity: 0;
        transform: translateY(12px) scale(.9)
      }

      to {
        opacity: 1;
        transform: translateY(0) scale(1)
      }
    }

    .hero-badge-dot {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      background: rgba(34, 197, 94, .15);
      flex-shrink: 0;
    }

    .dot {
      width: 8px;
      height: 8px;
      background: var(--ok);
      border-radius: 50%;
      animation: pulse 2s ease-in-out infinite;
      box-shadow: 0 0 8px var(--ok);
    }

    @keyframes pulse {

      0%,
      100% {
        transform: scale(1);
        opacity: 1;
        box-shadow: 0 0 8px var(--ok)
      }

      50% {
        transform: scale(1.5);
        opacity: .8;
        box-shadow: 0 0 16px var(--ok)
      }
    }

    /* name */
    .hero-name {
      font-size: clamp(2.8rem, 6vw, 5rem);
      font-weight: 900;
      color: var(--white);
      line-height: 1.0;
      letter-spacing: -.05em;
      margin-bottom: 14px;
    }

    .hero-name .grd {
      background: linear-gradient(135deg, #a5b4fc 0%, var(--p-lt) 25%, var(--ac) 60%, #34d399 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      background-size: 200% auto;
      animation: gradShift 4s linear infinite;
    }

    @keyframes gradShift {
      0% {
        background-position: 0% center
      }

      100% {
        background-position: 200% center
      }
    }

    /* role typewriter line */
    .hero-role-wrap {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 16px;
    }

    .hero-role-line {
      width: 40px;
      height: 2px;
      background: linear-gradient(90deg, var(--p), var(--ac));
      border-radius: 1px;
      flex-shrink: 0;
    }

    .hero-role {
      font-size: clamp(1rem, 2vw, 1.35rem);
      font-weight: 600;
      color: var(--txt2);
      font-family: var(--mono);
      letter-spacing: -.01em;
      margin-bottom: 0;
    }

    .hero-role .tw::after {
      content: '_';
      color: var(--p);
      animation: blink .8s step-end infinite;
      font-weight: 300;
    }

    @keyframes blink {
      50% {
        opacity: 0
      }
    }

    /* stack chips row */
    .hero-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 28px;
    }

    .hero-chip {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 12px;
      border-radius: 8px;
      font-size: .72rem;
      font-weight: 700;
      font-family: var(--mono);
      border: 1px solid;
      transition: all .2s;
      cursor: default;
    }

    .hero-chip.php {
      background: rgba(119, 123, 180, .1);
      border-color: rgba(119, 123, 180, .3);
      color: #9fa4d4
    }

    .hero-chip.larv {
      background: rgba(249, 50, 50, .08);
      border-color: rgba(249, 50, 50, .25);
      color: #fca5a5
    }

    .hero-chip.ci {
      background: rgba(6, 182, 212, .08);
      border-color: rgba(6, 182, 212, .25);
      color: #67e8f9
    }

    .hero-chip.db {
      background: rgba(245, 158, 11, .08);
      border-color: rgba(245, 158, 11, .25);
      color: #fcd34d
    }

    .hero-chip.api {
      background: rgba(34, 197, 94, .07);
      border-color: rgba(34, 197, 94, .22);
      color: #86efac
    }

    .hero-chip:hover {
      transform: translateY(-2px);
      filter: brightness(1.2)
    }

    /* description */
    .hero-desc {
      font-size: .93rem;
      color: var(--txt2);
      line-height: 1.9;
      max-width: 600px;
      margin-bottom: 38px;
      border-left: 2px solid rgba(99, 102, 241, .3);
      padding-left: 16px;
    }

    /* buttons */
    .hero-btns {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 52px
    }

    .btn-primary {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      padding: 13px 28px;
      background: linear-gradient(135deg, var(--p), var(--p-dk));
      color: #fff;
      border-radius: 50px;
      font-weight: 700;
      font-size: .87rem;
      border: none;
      cursor: pointer;
      box-shadow: 0 0 0 0 rgba(99, 102, 241, .4);
      transition: all var(--tr);
      position: relative;
      overflow: hidden;
      animation: btnGlow 3s ease-in-out infinite;
    }

    @keyframes btnGlow {

      0%,
      100% {
        box-shadow: 0 0 20px rgba(99, 102, 241, .35), 0 4px 20px rgba(99, 102, 241, .25)
      }

      50% {
        box-shadow: 0 0 40px rgba(99, 102, 241, .6), 0 4px 30px rgba(99, 102, 241, .4)
      }
    }

    .btn-primary::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255, 255, 255, .15), transparent);
      border-radius: inherit;
    }

    .btn-primary::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, var(--p-lt), var(--ac));
      opacity: 0;
      transition: opacity var(--tr);
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      animation: none;
      box-shadow: 0 0 50px rgba(99, 102, 241, .7), 0 8px 32px rgba(99, 102, 241, .4)
    }

    .btn-primary:hover::after {
      opacity: 1
    }

    .btn-primary span {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      gap: 9px
    }

    .btn-ghost {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      padding: 12px 24px;
      background: rgba(99, 102, 241, .06);
      color: var(--p-lt);
      border: 1.5px solid rgba(99, 102, 241, .3);
      border-radius: 50px;
      font-weight: 600;
      font-size: .87rem;
      cursor: pointer;
      transition: all var(--tr);
      backdrop-filter: blur(8px);
    }

    .btn-ghost:hover {
      background: rgba(99, 102, 241, .15);
      border-color: var(--p);
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(99, 102, 241, .2);
    }

    /* stats row */
    .hero-stats {
      display: flex;
      flex-wrap: wrap;
      align-items: stretch;
      gap: 0;
      background: rgba(255, 255, 255, .02);
      border: 1px solid var(--bdr);
      border-radius: 16px;
      overflow: hidden;
      max-width: 580px;
    }

    .stat-item {
      flex: 1;
      min-width: 100px;
      padding: 18px 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      position: relative;
      transition: background var(--tr);
    }

    .stat-item:not(:last-child)::after {
      content: '';
      position: absolute;
      right: 0;
      top: 20%;
      bottom: 20%;
      width: 1px;
      background: var(--bdr2);
    }

    .stat-item:hover {
      background: rgba(99, 102, 241, .07)
    }

    .stat-n {
      display: block;
      font-size: 1.8rem;
      font-weight: 900;
      color: var(--white);
      letter-spacing: -.04em;
      line-height: 1;
      margin-bottom: 4px;
      background: linear-gradient(135deg, var(--white), var(--p-lt));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .stat-l {
      font-size: .7rem;
      color: var(--txt3);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .06em
    }

    .stat-div {
      display: none
    }

    /* ════════════════════════════════════════════════════
   TECH STACK STRIP
════════════════════════════════════════════════════ */
    #stack {
      padding: 32px 64px;
      background: var(--bg2);
      border-top: 1px solid var(--bdr2);
      border-bottom: 1px solid var(--bdr2);
    }

    .stack-label {
      font-size: .7rem;
      color: var(--txt3);
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .1em;
      margin-bottom: 16px
    }

    .stack-pills {
      display: flex;
      flex-wrap: wrap;
      gap: 10px
    }

    .pill {
      padding: 6px 14px;
      border-radius: 8px;
      background: var(--card2);
      border: 1px solid var(--bdr);
      font-size: .76rem;
      font-weight: 600;
      color: var(--txt2);
      font-family: var(--mono);
      transition: all .2s;
    }

    .pill:hover {
      color: var(--p-lt);
      border-color: rgba(99, 102, 241, .4);
      background: rgba(99, 102, 241, .08)
    }

    /* ════════════════════════════════════════════════════
   ABOUT / SKILLS
════════════════════════════════════════════════════ */
    #about {
      background: var(--bg2)
    }

    .about-grid {
      display: grid;
      grid-template-columns: 1fr;
      align-items: start
    }

    .about-grid>.about-content {
      width: 100%;
      max-width: 980px
    }

    .about-bio {
      font-size: .91rem;
      color: var(--txt2);
      line-height: 1.9;
      margin-bottom: 28px
    }

    .about-meta {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      margin-bottom: 0
    }

    .meta-card {
      background: var(--card);
      border: 1px solid var(--bdr);
      border-radius: var(--r);
      padding: 16px 18px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .meta-icon {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      flex-shrink: 0;
      background: rgba(99, 102, 241, .12);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--p-lt);
      font-size: 1rem;
    }

    .meta-card strong {
      display: block;
      font-size: .82rem;
      font-weight: 700;
      color: var(--white)
    }

    .meta-card span {
      font-size: .75rem;
      color: var(--txt3)
    }

    /* skills — tag cloud cards (no bars/ratings) */
    .skills-wrap {
      display: flex;
      flex-direction: column;
      gap: 20px;
      margin-top: 4px
    }

    .skill-cat {
      background: var(--card);
      border: 1px solid var(--bdr);
      border-radius: var(--r);
      padding: 18px 20px
    }

    .skill-cat-label {
      font-size: .68rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .1em;
      color: var(--txt3);
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 7px;
    }

    .skill-cat-label i {
      font-size: .85rem
    }

    .skill-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 8px
    }

    .sk-tag {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 7px 14px;
      border-radius: 9px;
      font-size: .78rem;
      font-weight: 600;
      font-family: var(--mono);
      color: var(--p-lt);
      background: rgba(99, 102, 241, .1);
      border: 1px solid rgba(99, 102, 241, .26);
      cursor: default;
      transition: all .22s;
    }

    .sk-tag:hover {
      transform: translateY(-2px);
      filter: brightness(1.18)
    }

    /* colour variants */
    .sk-php {
      background: rgba(119, 123, 180, .1);
      border-color: rgba(119, 123, 180, .28);
      color: #a5b4fc
    }

    .sk-larv {
      background: rgba(249, 50, 50, .08);
      border-color: rgba(249, 50, 50, .22);
      color: #fca5a5
    }

    .sk-ci {
      background: rgba(6, 182, 212, .08);
      border-color: rgba(6, 182, 212, .22);
      color: #67e8f9
    }

    .sk-db {
      background: rgba(245, 158, 11, .08);
      border-color: rgba(245, 158, 11, .22);
      color: #fcd34d
    }

    .sk-api {
      background: rgba(34, 197, 94, .07);
      border-color: rgba(34, 197, 94, .20);
      color: #86efac
    }

    .sk-fe {
      background: rgba(168, 85, 247, .08);
      border-color: rgba(168, 85, 247, .22);
      color: #d8b4fe
    }

    .sk-int {
      background: rgba(236, 72, 153, .07);
      border-color: rgba(236, 72, 153, .22);
      color: #f9a8d4
    }

    .sk-dev {
      background: rgba(14, 165, 233, .08);
      border-color: rgba(14, 165, 233, .22);
      color: #7dd3fc
    }

    /* ════════════════════════════════════════════════════
   EXPERIENCE
════════════════════════════════════════════════════ */
    #experience {}

    .exp-timeline {
      margin-top: 44px;
      position: relative;
      padding-left: 28px
    }

    .exp-timeline::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 2px;
      background: linear-gradient(to bottom, var(--p), var(--ac), transparent);
      border-radius: 1px;
    }

    .exp-item {
      position: relative;
      margin-bottom: 44px
    }

    .exp-item:last-child {
      margin-bottom: 0
    }

    .exp-dot {
      position: absolute;
      left: -35px;
      top: 4px;
      width: 14px;
      height: 14px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--p), var(--ac));
      box-shadow: 0 0 12px rgba(99, 102, 241, .5);
      border: 2px solid var(--bg);
      flex-shrink: 0;
    }

    .exp-card {
      background: var(--card);
      border: 1px solid var(--bdr);
      border-radius: var(--r);
      padding: 24px 26px;
      transition: all var(--tr);
    }

    .exp-card:hover {
      border-color: rgba(99, 102, 241, .35);
      transform: translateX(4px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, .25)
    }

    .exp-head {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
      margin-bottom: 6px
    }

    .exp-company {
      font-size: 1.05rem;
      font-weight: 800;
      color: var(--white)
    }

    .exp-period {
      font-size: .72rem;
      font-weight: 700;
      background: rgba(99, 102, 241, .1);
      border: 1px solid var(--bdr);
      color: var(--p-lt);
      padding: 4px 12px;
      border-radius: 20px;
      white-space: nowrap;
      font-family: var(--mono);
    }

    .exp-role {
      font-size: .83rem;
      color: var(--ac);
      font-weight: 600;
      margin-bottom: 16px
    }

    .exp-points {
      padding-left: 0
    }

    .exp-points li {
      font-size: .84rem;
      color: var(--txt2);
      line-height: 1.75;
      padding: 5px 0 5px 20px;
      position: relative;
      border-bottom: 1px solid rgba(255, 255, 255, .03);
    }

    .exp-points li:last-child {
      border-bottom: none
    }

    .exp-points li::before {
      content: '';
      position: absolute;
      left: 0;
      top: 14px;
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--p), var(--ac));
      flex-shrink: 0;
    }

    /* ════════════════════════════════════════════════════
   SERVICES
════════════════════════════════════════════════════ */
    #services {
      background: var(--bg2)
    }

    .srv-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
      gap: 18px;
      margin-top: 44px
    }

    .srv-card {
      background: var(--card);
      border: 1px solid var(--bdr);
      border-radius: var(--r);
      padding: 28px 24px;
      transition: all var(--tr);
      position: relative;
      overflow: hidden;
      cursor: default;
    }

    .srv-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, var(--p), var(--ac));
      transform: scaleX(0);
      transform-origin: left;
      transition: transform var(--tr);
    }

    .srv-card:hover {
      border-color: rgba(99, 102, 241, .35);
      transform: translateY(-6px);
      box-shadow: 0 24px 48px rgba(0, 0, 0, .3)
    }

    .srv-card:hover::before {
      transform: scaleX(1)
    }

    .srv-ico {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      background: rgba(99, 102, 241, .1);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.35rem;
      color: var(--p-lt);
      margin-bottom: 18px;
      transition: all var(--tr);
    }

    .srv-card:hover .srv-ico {
      background: rgba(99, 102, 241, .2);
      transform: rotate(-8deg) scale(1.05)
    }

    .srv-t {
      font-size: .93rem;
      font-weight: 700;
      color: var(--white);
      margin-bottom: 8px
    }

    .srv-d {
      font-size: .81rem;
      color: var(--txt3);
      line-height: 1.7
    }

    /* ════════════════════════════════════════════════════
   PROJECTS
════════════════════════════════════════════════════ */
    #portfolio {}

    .proj-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 18px;
      margin-top: 44px
    }

    .proj-card {
      background: var(--card);
      border: 1px solid var(--bdr);
      border-radius: var(--r);
      padding: 24px;
      transition: all var(--tr);
      display: flex;
      flex-direction: column;
    }

    .proj-card:hover {
      border-color: rgba(99, 102, 241, .35);
      transform: translateY(-5px);
      box-shadow: 0 24px 48px rgba(0, 0, 0, .3)
    }

    .proj-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 12px
    }

    .proj-tech-badge {
      font-size: .68rem;
      font-weight: 700;
      font-family: var(--mono);
      padding: 3px 10px;
      border-radius: 6px;
      background: rgba(6, 182, 212, .1);
      border: 1px solid rgba(6, 182, 212, .25);
      color: var(--ac);
      white-space: nowrap;
    }

    .proj-h {
      font-size: .97rem;
      font-weight: 800;
      color: var(--white);
      margin-bottom: 8px
    }

    .proj-d {
      font-size: .81rem;
      color: var(--txt3);
      line-height: 1.7;
      flex: 1;
      margin-bottom: 16px
    }

    .proj-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: auto
    }

    .proj-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 5px
    }

    .proj-tag {
      font-size: .68rem;
      font-weight: 600;
      padding: 2px 9px;
      border-radius: 5px;
      background: rgba(99, 102, 241, .08);
      border: 1px solid rgba(99, 102, 241, .18);
      color: var(--txt3);
    }

    .proj-link {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: .78rem;
      font-weight: 700;
      color: var(--p-lt);
      border: 1px solid var(--bdr);
      border-radius: 8px;
      padding: 5px 12px;
      transition: all .2s;
    }

    .proj-link:hover {
      background: rgba(99, 102, 241, .12);
      border-color: var(--p)
    }

    .proj-icon-wrap {
      width: 42px;
      height: 42px;
      border-radius: 10px;
      background: rgba(99, 102, 241, .08);
      border: 1px solid var(--bdr);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--p-lt);
      font-size: 1.15rem;
      flex-shrink: 0;
    }

    /* ════════════════════════════════════════════════════
   CONTACT
════════════════════════════════════════════════════ */
    #contact {
      background: var(--bg2)
    }

    .contact-wrap {
      display: grid;
      grid-template-columns: 1fr 1.5fr;
      gap: 44px;
      margin-top: 44px
    }

    .ci-list {
      display: flex;
      flex-direction: column;
      gap: 14px;
      margin-bottom: 32px
    }

    .ci-row {
      background: var(--card);
      border: 1px solid var(--bdr);
      border-radius: var(--r);
      padding: 18px 20px;
      display: flex;
      align-items: center;
      gap: 14px;
      transition: all var(--tr);
    }

    .ci-row:hover {
      border-color: rgba(99, 102, 241, .35);
      transform: translateX(5px)
    }

    .ci-ico {
      width: 42px;
      height: 42px;
      border-radius: 11px;
      flex-shrink: 0;
      background: rgba(99, 102, 241, .1);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--p-lt);
      font-size: 1.05rem;
    }

    .ci-row strong {
      display: block;
      font-size: .72rem;
      font-weight: 700;
      color: var(--txt3);
      text-transform: uppercase;
      letter-spacing: .07em;
      margin-bottom: 3px
    }

    .ci-row span {
      font-size: .85rem;
      color: var(--txt)
    }

    /* form */
    .fc {
      background: var(--card);
      border: 1px solid var(--bdr);
      border-radius: var(--r);
      padding: 32px 28px
    }

    .frow {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 13px
    }

    .fg {
      margin-bottom: 14px
    }

    .fg label {
      display: block;
      font-size: .74rem;
      font-weight: 600;
      color: var(--txt3);
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: .05em
    }

    .fi {
      width: 100%;
      padding: 11px 15px;
      background: rgba(255, 255, 255, .03);
      border: 1.5px solid rgba(255, 255, 255, .07);
      border-radius: 9px;
      color: var(--txt);
      font-family: var(--font);
      font-size: .85rem;
      outline: none;
      transition: border-color .22s, box-shadow .22s;
    }

    .fi::placeholder {
      color: rgba(100, 116, 139, .5)
    }

    .fi:focus {
      border-color: var(--p);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
      background: rgba(99, 102, 241, .04)
    }

    textarea.fi {
      resize: vertical;
      min-height: 108px
    }

    .ferr {
      color: #f87171;
      font-size: .72rem;
      margin-top: 4px;
      display: block
    }

    .btn-send {
      width: 100%;
      padding: 13px;
      background: linear-gradient(135deg, var(--p), var(--p-dk));
      color: #fff;
      border: none;
      border-radius: 9px;
      font-family: var(--font);
      font-size: .88rem;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 6px 20px rgba(99, 102, 241, .28);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
      transition: all var(--tr);
    }

    .btn-send:hover {
      box-shadow: 0 10px 30px rgba(99, 102, 241, .5);
      transform: translateY(-2px)
    }

    .toast {
      display: none;
      padding: 11px 15px;
      border-radius: 9px;
      font-size: .83rem;
      font-weight: 500;
      margin-bottom: 14px;
      align-items: center;
      gap: 8px;
    }

    .toast.show {
      display: flex
    }

    .toast.ok {
      background: rgba(34, 197, 94, .1);
      border: 1px solid rgba(34, 197, 94, .25);
      color: #86efac
    }

    .toast.err {
      background: rgba(239, 68, 68, .1);
      border: 1px solid rgba(239, 68, 68, .25);
      color: #fca5a5
    }

    /* map */
    .map-wrap {
      border-radius: var(--r);
      overflow: hidden;
      border: 1px solid var(--bdr);
      margin-top: 14px
    }

    .map-wrap iframe {
      display: block;
      width: 100%;
      border: 0;
      filter: invert(90%) hue-rotate(180deg)
    }

    /* ════════════════════════════════════════════════════
   FOOTER
════════════════════════════════════════════════════ */
    footer {
      margin-left: var(--nav);
      background: var(--bg);
      border-top: 1px solid var(--bdr2);
      padding: 28px 64px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 14px;
    }

    footer p {
      font-size: .8rem;
      color: var(--txt3)
    }

    footer p span {
      color: var(--p-lt)
    }

    .f-links {
      display: flex;
      gap: 20px
    }

    .f-links a {
      font-size: .8rem;
      color: var(--txt3);
      transition: color .2s
    }

    .f-links a:hover {
      color: var(--p-lt)
    }

    /* back top */
    #btt {
      position: fixed;
      bottom: 26px;
      right: 26px;
      z-index: 400;
      width: 44px;
      height: 44px;
      border-radius: 11px;
      background: linear-gradient(135deg, var(--p), var(--p-dk));
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 1.05rem;
      cursor: pointer;
      box-shadow: 0 6px 20px rgba(99, 102, 241, .35);
      opacity: 0;
      pointer-events: none;
      transform: translateY(10px);
      transition: all .3s;
    }

    #btt.show {
      opacity: 1;
      pointer-events: auto;
      transform: translateY(0)
    }

    #btt:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 28px rgba(99, 102, 241, .5)
    }

    /* ════════════════════════════════════════════════════
   RESPONSIVE
════════════════════════════════════════════════════ */
    @media(max-width:1024px) {
      section {
        padding: 72px 40px
      }

      #home {
        padding: 0 40px
      }

      #stack {
        padding: 28px 40px
      }

      footer {
        padding: 24px 40px
      }

      .about-grid {
        gap: 36px
      }

      .contact-wrap {
        grid-template-columns: 1fr
      }

      .hero-inner {
        max-width: 850px;
        grid-template-columns: 1fr;
        gap: 24px
      }

      .hero-visual {
        display: none
      }
    }

    @media(max-width:860px) {
      .sidenav {
        display: none
      }

      .topbar {
        display: flex
      }

      .pw {
        margin-left: 0;
        padding-top: 58px
      }

      footer {
        margin-left: 0
      }

      section {
        padding: 60px 20px
      }

      #home {
        padding: 32px 20px
      }

      .hero-inner {
        max-width: 760px;
        grid-template-columns: 1fr
      }

      .hero-visual {
        display: none
      }

      #stack {
        padding: 24px 20px
      }

      footer {
        padding: 20px
      }

      .about-grid {
        grid-template-columns: 1fr
      }

      .hero-stats {
        max-width: 100%
      }

      .stat-item {
        padding: 14px 12px
      }

      .stat-n {
        font-size: 1.5rem
      }

      .frow {
        grid-template-columns: 1fr
      }

      .about-meta {
        grid-template-columns: 1fr
      }

      .hero-desc {
        border-left: none;
        padding-left: 0
      }

      .hero-chips {
        gap: 6px
      }
    }

    @media(max-width:520px) {
      .hero-name {
        font-size: 2.2rem
      }

      .sec-h {
        font-size: 1.55rem
      }

      .srv-grid,
      .proj-grid {
        grid-template-columns: 1fr
      }
    }

    @media(prefers-reduced-motion:reduce) {
      .visual-float {
        animation: none
      }
    }
  </style>
</head>

<body>
  <!-- Preloader -->
  <div id="pre">
    <div class="pre-ring"></div>
  </div>
  <!-- Canvas -->
  <canvas id="bgCanvas"></canvas>
  <?php $appBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/.'); ?>

  <!-- ── Side Nav ──────────────────────────────────────────── -->
  <nav class="sidenav">
    <a href="<?= e($appBasePath . '/home') ?>" class="sn-logo" aria-label="TechShyam home">SY</a>
    <ul class="sn-links">
      <?php
      $navs = [
        ['home', 'bi-house-fill', 'Home'],
        ['about', 'bi-person-fill', 'About'],
        ['experience', 'bi-briefcase-fill', 'Experience'],
        ['services', 'bi-grid-fill', 'Services'],
        ['portfolio', 'bi-columns-gap', 'Projects'],
        ['contact', 'bi-envelope-fill', 'Contact'],
      ];
      foreach ($navs as [$sec, $ico, $lbl]):
      ?>
        <li><a href="<?= e($appBasePath . '/' . $sec) ?>" class="sn-item <?= $sec === 'home' ? 'active' : '' ?>" data-s="<?= $sec ?>" aria-label="<?= e($lbl) ?>" title="<?= e($lbl) ?>">
            <i class="bi <?= $ico ?>"></i>
            <span class="sn-tip"><?= $lbl ?></span>
          </a></li>
      <?php endforeach; ?>
    </ul>
    <div class="sn-social">
      <?php
      $socialIcons = ['linkedin' => 'bi-linkedin', 'github' => 'bi-github', 'twitter' => 'bi-twitter-x', 'instagram' => 'bi-instagram', 'facebook' => 'bi-facebook'];
      foreach ($social as $k => $url): if (!$url) continue;
        $ico = $socialIcons[$k] ?? 'bi-link-45deg';
      ?>
        <a href="<?= e($url) ?>" target="_blank" rel="noopener noreferrer" class="sn-soc" aria-label="<?= e(ucfirst($k)) ?>" title="<?= e(ucfirst($k)) ?>"><i class="bi <?= $ico ?>" aria-hidden="true"></i></a>
      <?php endforeach; ?>
    </div>
  </nav>

  <!-- ── Mobile Topbar ─────────────────────────────────────── -->
  <div class="topbar">
    <span class="topbar-brand">SY.</span>
    <button class="ham" id="ham"><i class="bi bi-list" id="hamIco"></i></button>
  </div>
  <nav class="mob-menu" id="mobMenu">
    <?php foreach ($navs as [$sec, $ico, $lbl]): ?>
      <a href="<?= e($appBasePath . '/' . $sec) ?>" class="mob-item" onclick="closeMob()">
        <i class="bi <?= $ico ?>"></i> <?= $lbl ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- ── Page ───────────────────────────────────────────────── -->
  <div class="pw">

    <!-- ╔══════════════════════════╗
     HERO
╚══════════════════════════╝ -->
    <section id="home">
      <div class="hero-grid-lines"></div>
      <div class="hero-inner">
        <div class="hero-copy">

          <!-- Availability badge -->
          <div class="hero-badge rv d1">
            <span class="hero-badge-dot"><span class="dot"></span></span>
            Available for Opportunities
          </div>

          <!-- Name -->
          <h1 class="hero-name rv d2">
            <?php
            $parts = explode(' ', $profile['name'] ?? 'Shyam Milan Yadav');
            $last  = array_pop($parts);
            echo implode(' ', $parts) . '<br><span class="grd">' . htmlspecialchars($last, ENT_QUOTES) . '</span>';
            ?>
          </h1>

          <!-- Typewriter role -->
          <div class="hero-role-wrap rv d3">
            <span class="hero-role-line"></span>
            <p class="hero-role"><span class="tw" id="tw"></span></p>
          </div>

          <!-- Tech chips -->
          <div class="hero-chips rv d3">
            <span class="hero-chip php"><i class="bi bi-filetype-php"></i> Core PHP</span>
            <span class="hero-chip larv"><i class="bi bi-hurricane"></i> Laravel</span>
            <span class="hero-chip ci"><i class="bi bi-fire"></i> CodeIgniter</span>
            <span class="hero-chip db"><i class="bi bi-database-fill"></i> MySQL / PostgreSQL</span>
            <span class="hero-chip api"><i class="bi bi-plug-fill"></i> REST APIs</span>
          </div>

          <!-- Description -->
          <p class="hero-desc rv d4"><?= e($profile['tagline'] ?? '') ?></p>

          <!-- Buttons -->
          <div class="hero-btns rv d5">
            <a href="<?= e($appBasePath . '/contact') ?>" class="btn-primary">
              <span><i class="bi bi-send-fill"></i> Hire Me</span>
            </a>
            <?php if (!empty($profile['resume'])): ?>
              <a href="<?= e($profile['resume']) ?>" class="btn-ghost" download>
                <i class="bi bi-file-earmark-person"></i> Download CV
              </a>
            <?php endif; ?>
            <a href="<?= e($appBasePath . '/portfolio') ?>" class="btn-ghost">
              <i class="bi bi-grid-1x2-fill"></i> View Work
            </a>
          </div>

          <!-- Stats card row -->
          <div class="hero-stats rv d6">
            <div class="stat-item">
              <span class="stat-n" data-count="4">0</span>
              <span class="stat-l">Years Exp.</span>
            </div>
            <div class="stat-item">
              <span class="stat-n" data-count="<?= count($projects) ?>">0</span>
              <span class="stat-l">Projects</span>
            </div>
            <div class="stat-item">
              <span class="stat-n" data-count="3">0</span>
              <span class="stat-l">Companies</span>
            </div>
            <div class="stat-item">
              <span class="stat-n" data-count="10">0</span>
              <span class="stat-l">Clients</span>
            </div>
          </div>

        </div>
        <div class="hero-visual" aria-hidden="true">
          <div class="visual-orbit"></div>
          <div class="visual-card">
            <div class="visual-card-top"><span class="window-dot"></span><span class="window-dot"></span><span class="window-dot"></span><span class="visual-file">developer.php</span></div>
            <div class="visual-code">
              <div class="code-lines">01<br>02<br>03<br>04<br>05<br>06<br>07</div>
              <div class="code-content"><span class="code-purple">class</span> Developer <span class="code-muted">{</span><br>&nbsp;&nbsp;<span class="code-cyan">$role</span> = <span class="code-green">'Backend Engineer'</span>;<br>&nbsp;&nbsp;<span class="code-cyan">$stack</span> = [<span class="code-yellow">'PHP'</span>, <span class="code-yellow">'Laravel'</span>];<br><br>&nbsp;&nbsp;<span class="code-purple">function</span> <span class="code-cyan">build</span>() {<br>&nbsp;&nbsp;&nbsp;&nbsp;<span class="code-purple">return</span> <span class="code-green">'reliable software'</span>;<br>&nbsp;&nbsp;}<br><span class="code-muted">}</span></div>
            </div>
            <div class="visual-card-foot"><span>Crafting dependable web apps</span><span class="visual-status"><i class="bi bi-circle-fill"></i> Open to work</span></div>
          </div>
          <div class="visual-float visual-float-top"><i class="bi bi-braces"></i> PHP / Laravel</div>
          <div class="visual-float visual-float-bottom"><i class="bi bi-diagram-3-fill"></i> APIs / Integrations</div>
        </div>

      </div>
    </section>

    <!-- ╔══════════════════════════╗
     TECH STACK STRIP
╚══════════════════════════╝ -->
    <div id="stack">
      <div class="stack-label">Core Technical Stack</div>
      <div class="stack-pills">
        <?php
        $techs = [
          'Core PHP',
          'Laravel',
          'CodeIgniter',
          'MySQL',
          'PostgreSQL',
          'MongoDB',
          'RESTful APIs',
          'RBAC',
          'Docker',
          'AWS EC2/S3',
          'Redis',
          'Git/GitHub',
          'JavaScript',
          'jQuery',
          'Bootstrap',
          'AJAX',
          'Razorpay',
          'Stripe'
        ];
        foreach ($techs as $t):
        ?><span class="pill"><?= e($t) ?></span><?php endforeach; ?>
      </div>
    </div>

    <!-- ╔══════════════════════════╗
     ABOUT
╚══════════════════════════╝ -->
    <section id="about">
      <div class="about-grid">

        <!-- Left: bio + meta -->
        <div class="rvl about-content">
          <div class="sec-tag"><i class="bi bi-person-fill"></i> About Me</div>
          <h2 class="sec-h">Who I <span>Am</span></h2>
          <p class="about-bio"><?= e($profile['bio'] ?? '') ?></p>
          <div class="about-meta">
            <div class="meta-card">
              <div class="meta-icon"><i class="bi bi-mortarboard-fill"></i></div>
              <div>
                <strong><?= e($edu['degree'] ?? 'BCA') ?></strong>
                <span><?= e($edu['university'] ?? '') ?></span>
              </div>
            </div>
            <div class="meta-card">
              <div class="meta-icon"><i class="bi bi-calendar-check-fill"></i></div>
              <div>
                <strong>Graduation</strong>
                <span><?= e($edu['year'] ?? '2016–2019') ?></span>
              </div>
            </div>
            <div class="meta-card">
              <div class="meta-icon"><i class="bi bi-geo-alt-fill"></i></div>
              <div>
                <strong>Location</strong>
                <span><?= e($profile['address'] ?? '') ?></span>
              </div>
            </div>
            <div class="meta-card">
              <div class="meta-icon"><i class="bi bi-translate"></i></div>
              <div>
                <strong>Languages</strong>
                <span>Hindi, English</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- ╔══════════════════════════╗
     EXPERIENCE
╚══════════════════════════╝ -->
    <section id="experience">
      <div class="rv">
        <div class="sec-tag"><i class="bi bi-briefcase-fill"></i> Career</div>
        <h2 class="sec-h">Professional <span>Experience</span></h2>
        <p class="sec-sub">4+ years delivering production-grade web applications across fintech, healthcare, publishing, and education sectors.</p>
      </div>
      <div class="exp-timeline">
        <?php foreach ($exp as $i => $job): ?>
          <div class="exp-item rv d<?= min($i + 1, 6) ?>">
            <div class="exp-dot"></div>
            <div class="exp-card">
              <div class="exp-head">
                <span class="exp-company"><?= e($job['company']) ?></span>
                <span class="exp-period"><?= e($job['period']) ?></span>
              </div>
              <div class="exp-role"><i class="bi bi-diamond-fill" style="font-size:.55rem;margin-right:6px"></i><?= e($job['role']) ?></div>
              <ul class="exp-points">
                <?php foreach ($job['points'] as $pt): ?>
                  <li><?= e($pt) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ╔══════════════════════════╗
     SERVICES
╚══════════════════════════╝ -->
    <section id="services">
      <div class="rv">
        <div class="sec-tag"><i class="bi bi-grid-fill"></i> What I Offer</div>
        <h2 class="sec-h">My <span>Services</span></h2>
        <p class="sec-sub">End-to-end backend engineering from database architecture to cloud deployment.</p>
      </div>
      <div class="srv-grid">
        <?php foreach ($services as $i => $s): ?>
          <div class="srv-card rv d<?= min($i + 1, 6) ?>">
            <div class="srv-ico"><i class="bi <?= e($s['icon']) ?>"></i></div>
            <div class="srv-t"><?= e($s['title']) ?></div>
            <div class="srv-d"><?= e($s['description']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ╔══════════════════════════╗
     PROJECTS
╚══════════════════════════╝ -->
    <section id="portfolio">
      <div class="rv">
        <div class="sec-tag"><i class="bi bi-columns-gap"></i> Portfolio</div>
        <h2 class="sec-h">Featured <span>Projects</span></h2>
        <p class="sec-sub">Real-world production applications across diverse industries — each solving complex business problems.</p>
      </div>
      <div class="proj-grid">
        <?php foreach ($projects as $i => $p):
          $techColor = ['Laravel' => 'rgba(239,68,68,.12)', 'CodeIgniter' => 'rgba(6,182,212,.1)'];
          $techBdr   = ['Laravel' => 'rgba(239,68,68,.28)', 'CodeIgniter' => 'rgba(6,182,212,.28)'];
          $techTxt   = ['Laravel' => '#fca5a5',             'CodeIgniter' => '#67e8f9'];
          $t = $p['tech'] ?? 'PHP';
        ?>
          <div class="proj-card rv d<?= min(($i % 3) + 1, 6) ?>">
            <div class="proj-top">
              <div class="proj-icon-wrap"><i class="bi bi-code-square"></i></div>
              <span class="proj-tech-badge" style="background:<?= $techColor[$t] ?? 'rgba(99,102,241,.1)' ?>;border-color:<?= $techBdr[$t] ?? 'rgba(99,102,241,.25)' ?>;color:<?= $techTxt[$t] ?? 'var(--p-lt)' ?>">
                <?= e($t) ?>
              </span>
            </div>
            <div class="proj-h"><?= e($p['title']) ?></div>
            <div class="proj-d"><?= e($p['description']) ?></div>
            <div class="proj-footer">
              <div class="proj-tags">
                <?php foreach (array_slice($p['tags'] ?? [], 0, 3) as $tag): ?>
                  <span class="proj-tag"><?= e($tag) ?></span>
                <?php endforeach; ?>
              </div>
              <?php if (!empty($p['url'])): ?>
                <a href="<?= e($p['url']) ?>" target="_blank" rel="noopener" class="proj-link">
                  <i class="bi bi-box-arrow-up-right"></i> Visit
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ╔══════════════════════════╗
     CONTACT
╚══════════════════════════╝ -->
    <section id="contact">
      <div class="rv">
        <div class="sec-tag"><i class="bi bi-envelope-fill"></i> Contact</div>
        <h2 class="sec-h">Let's <span>Connect</span></h2>
        <p class="sec-sub">Open to full-time roles, freelance projects, and collaboration — reach out and I'll reply within 24 hours.</p>
      </div>
      <div class="contact-wrap">

        <!-- Info -->
        <div class="rvl d1">
          <div class="ci-list">
            <?php
            $cInfo = [
              ['bi-telephone-fill', 'Phone', implode(' · ', array_filter([$profile['phone1'] ?? '', $profile['phone2'] ?? '']))],
              ['bi-envelope-fill', 'Email', $profile['email'] ?? ''],
              ['bi-geo-alt-fill',  'Location', $profile['address'] ?? ''],
            ];
            foreach ($cInfo as [$ico, $lbl, $val]):
            ?>
              <div class="ci-row">
                <div class="ci-ico"><i class="bi <?= $ico ?>"></i></div>
                <div><strong><?= $lbl ?></strong><span><?= e($val) ?></span></div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="map-wrap">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3501.990538015637!2d77.37673801500891!3d28.630045682418817!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ceff8864e0cf1%3A0xa20290bf75099ebd!2sBSI%20Business%20Park%20H15!5e0!3m2!1sen!2sin!4v1677239001686!5m2!1sen!2sin"
              height="200" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          </div>
        </div>

        <!-- Form -->
        <div class="fc rvr d2">
          <div class="toast" id="toast">
            <i class="bi" id="toastIco"></i>
            <span id="toastMsg"></span>
          </div>
          <form id="cForm" novalidate>
            <div class="frow">
              <div class="fg">
                <label>Name</label>
                <input type="text" name="name" id="fn" class="fi" placeholder="Shyam Milan Yadav">
                <span class="ferr" id="fnErr"></span>
              </div>
              <div class="fg">
                <label>Email</label>
                <input type="email" name="email" id="fe" class="fi" placeholder="you@email.com">
                <span class="ferr" id="feErr"></span>
              </div>
            </div>
            <div class="frow">
              <div class="fg">
                <label>Phone</label>
                <input type="text" name="phone" id="fp" class="fi" placeholder="+91 000 000 0000">
                <span class="ferr" id="fpErr"></span>
              </div>
              <div class="fg">
                <label>Subject</label>
                <input type="text" name="subject" id="fs" class="fi" placeholder="Project inquiry…">
                <span class="ferr" id="fsErr"></span>
              </div>
            </div>
            <div class="fg">
              <label>Message</label>
              <textarea name="message" id="fm" class="fi" placeholder="Describe your project or opportunity…"></textarea>
              <span class="ferr" id="fmErr"></span>
            </div>
            <button type="submit" class="btn-send" id="sbtn">
              <i class="bi bi-send-fill"></i> Send Message
            </button>
          </form>
        </div>

      </div>
    </section>

  </div><!-- /.pw -->

  <!-- Footer -->
  <footer>
    <p>© <?= date('Y') ?> <span><?= $name ?></span>. All rights reserved.</p>
    <div class="f-links">
      <a href="/home">Home</a>
      <a href="/about">About</a>
      <a href="/experience">Experience</a>
      <a href="/portfolio">Projects</a>
      <a href="/contact">Contact</a>
    </div>
  </footer>

  <div id="btt" onclick="scrollTo({top:0,behavior:'smooth'})"><i class="bi bi-arrow-up"></i></div>

  <!-- ════════════════════════════════════════════════════
     JS
════════════════════════════════════════════════════ -->
  <script src="assets/js/jquery-3.2.1.min.js"></script>
  <script>
    /* ── Preloader ── */
    window.addEventListener('load', () => setTimeout(() => document.getElementById('pre').classList.add('hide'), 250));

    /* ── Particle canvas ── */
    (() => {
      const cv = document.getElementById('bgCanvas'),
        ctx = cv.getContext('2d');
      let W, H, pts = [];
      const resize = () => {
        W = cv.width = innerWidth;
        H = cv.height = innerHeight
      };
      resize();
      addEventListener('resize', resize);
      const mk = () => ({
        x: Math.random() * W,
        y: Math.random() * H,
        vx: (Math.random() - .5) * .35,
        vy: (Math.random() - .5) * .35,
        r: Math.random() * 1.4 + .4,
        a: Math.random() * .35 + .04,
        c: Math.random() > .5 ? '99,102,241' : '6,182,212'
      });
      for (let i = 0; i < 70; i++) pts.push(mk());
      const draw = () => {
        ctx.clearRect(0, 0, W, H);
        pts.forEach(p => {
          ctx.beginPath();
          ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
          ctx.fillStyle = `rgba(${p.c},${p.a})`;
          ctx.fill();
          p.x += p.vx;
          p.y += p.vy;
          if (p.x < 0 || p.x > W || p.y < 0 || p.y > H) Object.assign(p, mk());
        });
        for (let i = 0; i < pts.length; i++)
          for (let j = i + 1; j < pts.length; j++) {
            const dx = pts[i].x - pts[j].x,
              dy = pts[i].y - pts[j].y,
              d = Math.hypot(dx, dy);
            if (d < 110) {
              ctx.beginPath();
              ctx.moveTo(pts[i].x, pts[i].y);
              ctx.lineTo(pts[j].x, pts[j].y);
              ctx.strokeStyle = `rgba(99,102,241,${.12*(1-d/110)})`;
              ctx.lineWidth = .5;
              ctx.stroke()
            }
          }
        requestAnimationFrame(draw);
      };
      draw();
    })();

    /* ── Typewriter ── */
    (() => {
      const words = ['<?= addslashes($title) ?>', 'Senior PHP Developer', 'Backend Engineer', 'API Integration Expert', 'Problem Solver'];
      const el = document.getElementById('tw');
      let wi = 0,
        ci = 0,
        del = false;
      const t = () => {
        const w = words[wi],
          cur = del ? w.slice(0, ci--) : w.slice(0, ++ci);
        el.textContent = cur;
        let d = del ? 55 : 95;
        if (!del && ci === w.length) {
          d = 1800;
          del = true
        } else if (del && ci === 0) {
          del = false;
          wi = (wi + 1) % words.length;
          d = 380
        }
        setTimeout(t, d);
      };
      setTimeout(t, 700);
    })();

    /* ── Reveal on scroll ── */
    const ro = new IntersectionObserver(es => es.forEach(e => e.isIntersecting && e.target.classList.add('in')), {
      threshold: .1
    });
    document.querySelectorAll('.rv,.rvl,.rvr').forEach(el => ro.observe(el));

    /* ── Skill bars ── */
    /* ── Counter animation ── */
    (() => {
      const counters = document.querySelectorAll('.stat-n[data-count]');
      const countObs = new IntersectionObserver(entries => {
        entries.forEach(e => {
          if (!e.isIntersecting) return;
          const el = e.target;
          const target = parseInt(el.dataset.count, 10);
          const suffix = el.closest('.stat-item').querySelector('.stat-l').textContent.includes('Exp') ? '+' :
            target >= 10 ? '+' : '';
          let start = 0;
          const step = Math.ceil(target / 40);
          const timer = setInterval(() => {
            start = Math.min(start + step, target);
            el.textContent = start + suffix;
            if (start >= target) clearInterval(timer);
          }, 35);
          countObs.unobserve(el);
        });
      }, {
        threshold: .5
      });
      counters.forEach(c => countObs.observe(c));
    })();

    /* ── Mobile menu ── */
    function closeMob() {
      document.getElementById('mobMenu').classList.remove('open');
      document.getElementById('hamIco').className = 'bi bi-list';
    }
    document.getElementById('ham').addEventListener('click', () => {
      const open = document.getElementById('mobMenu').classList.toggle('open');
      document.getElementById('hamIco').className = open ? 'bi bi-x-lg' : 'bi bi-list';
    });

    /* ── Clean URL nav: intercept /section links → smooth scroll + pushState ── */
    const secs = document.querySelectorAll('section[id]');
    const snItems = document.querySelectorAll('.sn-item[data-s]');
    const SECTIONS = ['home', 'about', 'experience', 'services', 'portfolio', 'contact'];

    // Detect base path dynamically (works on both / and /techshyam/ subpaths)
    const BASE = (function() {
      // Walk up from current path until we find a segment that isn't a section
      const parts = location.pathname.replace(/\/+$/, '').split('/');
      // Remove any trailing section segment
      if (parts.length && SECTIONS.includes(parts[parts.length - 1])) parts.pop();
      return parts.join('/') || '';
    })();

    function sectionUrl(id) {
      return id === 'home' ? (BASE + '/') : (BASE + '/' + id);
    }

    // Intercept ALL internal section links (sidenav, mobile menu, buttons, footer)
    document.querySelectorAll('a[href]').forEach(a => {
      const href = a.getAttribute('href');
      // Match /sectionname or /sectionname/ (absolute) OR relative like /about
      const match = href && href.match(/(?:^|.*\/)([a-z]+)\/?$/);
      const seg = match && match[1];
      if (seg && SECTIONS.includes(seg)) {
        a.addEventListener('click', function(e) {
          e.preventDefault();
          scrollToSection(seg);
          history.pushState({
            section: seg
          }, '', sectionUrl(seg));
          closeMob();
        });
      }
    });

    function scrollToSection(id) {
      const target = document.getElementById(id);
      if (!target) return;
      const offset = window.innerWidth <= 860 ? 58 : 0;
      window.scrollTo({
        top: target.getBoundingClientRect().top + window.scrollY - offset,
        behavior: 'smooth'
      });
    }

    // Update URL bar and active nav item as user scrolls
    function updateNav() {
      let cur = 'home';
      secs.forEach(s => {
        if (window.scrollY >= s.offsetTop - 240) cur = s.id;
      });
      snItems.forEach(n => n.classList.toggle('active', n.dataset.s === cur));
      const newUrl = sectionUrl(cur);
      if (location.pathname !== newUrl) {
        history.replaceState({
          section: cur
        }, '', newUrl);
      }
    }
    addEventListener('scroll', updateNav, {
      passive: true
    });

    // On page load: if URL ends with a section name, scroll to it
    (function() {
      const parts = location.pathname.replace(/\/+$/, '').split('/');
      const last = parts[parts.length - 1];
      if (last && SECTIONS.includes(last) && last !== 'home') {
        setTimeout(() => scrollToSection(last), 400);
      }
    })();

    // Browser back / forward
    addEventListener('popstate', e => {
      const id = (e.state && e.state.section) || 'home';
      scrollToSection(id);
    });

    /* ── Back to top ── */
    const btt = document.getElementById('btt');
    addEventListener('scroll', () => btt.classList.toggle('show', scrollY > 400), {
      passive: true
    });

    /* ── Contact form ── */
    $('#cForm').on('submit', function(e) {
      e.preventDefault();
      const map = {
        fn: 'Name',
        fe: 'Email',
        fp: 'Phone',
        fs: 'Subject',
        fm: 'Message'
      };
      let ok = true;
      Object.entries(map).forEach(([id, lbl]) => {
        $('#' + id + 'Err').text('');
        if (!$('#' + id).val().trim()) {
          $('#' + id + 'Err').text(lbl + ' is required.');
          ok = false;
        }
      });
      if (!ok) return;

      const btn = $('#sbtn');
      btn.html('<i class="bi bi-hourglass-split"></i> Sending…').prop('disabled', true);

      $.ajax({
        url: 'action.php',
        method: 'POST',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData: false,
        success: function(r) {
          try {
            const j = typeof r === 'string' ? JSON.parse(r) : r;
            if (j.status === 'success') {
              showToast('ok', 'bi-check-circle-fill', 'Message sent! I\'ll reply within 24 hours.');
              document.getElementById('cForm').reset();
            } else {
              showToast('err', 'bi-exclamation-circle-fill', 'Something went wrong. Please try again.');
            }
          } catch (ex) {
            showToast('err', 'bi-exclamation-circle-fill', 'Unexpected error.');
          }
          btn.html('<i class="bi bi-send-fill"></i> Send Message').prop('disabled', false);
        },
        error: function() {
          showToast('err', 'bi-exclamation-circle-fill', 'Network error. Please try again.');
          btn.html('<i class="bi bi-send-fill"></i> Send Message').prop('disabled', false);
        }
      });

      function showToast(type, ico, msg) {
        const t = $('#toast');
        t.removeClass('ok err show').addClass(type + ' show');
        $('#toastIco').attr('class', 'bi ' + ico);
        $('#toastMsg').text(msg);
        setTimeout(() => t.removeClass('show'), 5000);
      }
    });
  </script>
</body>

</html>