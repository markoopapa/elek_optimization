Universal Performance & Accessibility Optimizer
A PrestaShop Module to Boost Core Web Vitals and WCAG Compliance
🚀 Overview
The Universal Performance & Accessibility Optimizer is a lightweight, high-impact PrestaShop module designed to solve common technical SEO and accessibility (a11y) issues. Instead of manually editing template files, this module centralizes critical fixes for Largest Contentful Paint (LCP), JavaScript execution errors, and UX navigation.

✨ Key Features
1. LCP Image Preload (Speed Optimization)
Automatically detects the Product Cover Image on product pages.

Injects a <link rel="preload"> tag into the HTML head.

Sets fetchpriority="high" to tell browsers to prioritize the image download immediately.

Significantly reduces Largest Contentful Paint (LCP) time.

2. jQuery "ReferenceError" Safety Layer
Prevents the common "$ is not defined" error.

Uses a polling mechanism to ensure the jQuery library is fully loaded before executing dependent scripts.

Ensures that third-party modules or inline scripts don't break the frontend experience.

3. Accessible Slick Slider Navigation
Fixes a major accessibility flaw in carousels (Slick Slider).

Automatically manages tabindex and aria-hidden attributes for hidden slides.

Prevents "ghost focus," where keyboard users (Tab key) or screen readers get stuck on invisible elements.

4. Enhanced Visual Contrast & Focus
Includes a global CSS layer to ensure high-contrast ratios for product flags (New, Sale).

Adds a clearly visible focus state for all interactive elements to comply with WCAG standards.

🛠 Installation
Download the repository as a .zip file.

In your PrestaShop Back Office, go to Modules > Module Manager.

Click Upload a module and select the elek_optimization.zip.

Install and click Configure.

⚙️ Configuration
The module provides a user-friendly dashboard where you can toggle specific optimizations:

LCP Preload: Enable/Disable automatic product image prioritization.

jQuery Fix: Turn on the safety wrapper for JavaScript execution.

Slider Fix: Enable automated accessibility management for carousels.

📁 File Structure
Plaintext
elek_optimization/
├── elek_optimization.php   # Core module logic & Hooks
├── views/
│   ├── css/
│   │   └── front.css       # Contrast & Focus styles
│   └── js/
│       └── front.js        # jQuery safety & Slider logic
└── README.md               # Documentation
👨‍💻 Author
Markoo Passionate about web performance and clean code.

📄 License
This project is licensed under the General Public License (GPL). Feel free to use, modify, and distribute it for any PrestaShop project.

Why use this?
Manually editing .tpl files is risky and changes are often lost during theme updates. This module uses PrestaShop Hooks, ensuring your optimizations remain intact even if you change your theme or update the core software.
