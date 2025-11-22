/**
 * Navigation Discovery Script
 * Maps out all sidebar menu items and buttons for comprehensive E2E testing
 */

const { chromium } = require('playwright');
const fs = require('fs');

const config = {
    baseUrl: process.env.APP_URL || 'https://audit.test',
    credentials: {
        email: 'admin@alfa-electronics.com',
        password: 'password'
    }
};

async function discoverNavigation() {
    const browser = await chromium.launch({ headless: false });
    const context = await browser.newContext({ ignoreHTTPSErrors: true });
    const page = await context.newPage();

    console.log('🔍 Starting navigation discovery...\n');

    try {
        // Login first
        console.log('🔐 Logging in...');
        await page.goto(config.baseUrl + '/login');
        await page.waitForLoadState('networkidle');
        await page.fill('input[name="email"]', config.credentials.email);
        await page.fill('input[name="password"]', config.credentials.password);
        await page.click('button[type="submit"]');

        // Wait for navigation after login
        await page.waitForTimeout(5000);
        await page.waitForLoadState('networkidle');

        // Go to dashboard
        await page.goto(config.baseUrl + '/');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);

        console.log('✅ Login successful\n');

        // Discover sidebar menu structure
        console.log('📋 SIDEBAR MENU STRUCTURE:\n');

        // Get all menu items from sidebar
        const menuItems = await page.evaluate(() => {
            const items = [];

            // Try multiple selectors for sidebar menu items
            const selectors = [
                '.menu-inner .menu-item a',
                '.sidebar .menu-item a',
                'aside .menu-item a',
                'nav .menu-item a',
                '.menu-vertical .menu-item a'
            ];

            let menuLinks = [];
            for (const selector of selectors) {
                menuLinks = document.querySelectorAll(selector);
                if (menuLinks.length > 0) break;
            }

            menuLinks.forEach((link, index) => {
                let text = link.textContent?.trim();
                // Remove extra whitespace and newlines
                text = text?.replace(/\s+/g, ' ').trim();

                const href = link.getAttribute('href');
                const isActive = link.classList.contains('active');
                const hasSubmenu = link.parentElement?.querySelector('.menu-sub') !== null;

                if (text && text.length > 0 && !text.includes('Language')) {
                    items.push({
                        index,
                        text,
                        href,
                        isActive,
                        hasSubmenu
                    });
                }
            });

            return items;
        });

        menuItems.forEach(item => {
            console.log(`${item.index + 1}. ${item.text}`);
            console.log(`   URL: ${item.href || 'N/A'}`);
            console.log(`   Has Submenu: ${item.hasSubmenu}`);
            console.log('');
        });

        // Save to JSON file
        const navigationMap = {
            discoveredAt: new Date().toISOString(),
            baseUrl: config.baseUrl,
            menuItems: menuItems,
            modules: []
        };

        // Discover buttons in each main module
        console.log('\n🔘 DISCOVERING BUTTONS IN EACH MODULE:\n');

        const modulesToTest = [
            { name: 'Dashboard', url: '/' },
            { name: 'Users', url: '/users' },
            { name: 'Sectors', url: '/sectors' },
            { name: 'Departments', url: '/departments' },
            { name: 'Audit Plans', url: '/audit-plans' },
            { name: 'Audit Questions', url: '/audit-questions' },
            { name: 'CheckList Groups', url: '/checklist-groups' },
            { name: 'Audit Execution', url: '/audit-responses' },
            { name: 'Audit Reports', url: '/audit-reports' },
            { name: 'CAR Management', url: '/cars' },
            { name: 'Customer Complaints', url: '/customer-complaints' },
            { name: 'External Audits', url: '/external-audits' },
            { name: 'ISO Certificates', url: '/certificates' }
        ];

        for (const module of modulesToTest) {
            console.log(`\n📦 Module: ${module.name}`);
            console.log(`   URL: ${config.baseUrl}${module.url}`);

            try {
                await page.goto(config.baseUrl + module.url, { timeout: 10000 });
                await page.waitForLoadState('networkidle', { timeout: 10000 });
                await page.waitForTimeout(1000);

                // Discover all buttons and interactive elements
                const buttons = await page.evaluate(() => {
                    const found = [];

                    // Find all buttons
                    document.querySelectorAll('button, a.btn, input[type="submit"]').forEach(btn => {
                        const text = btn.textContent?.trim() || btn.getAttribute('title') || '';
                        const type = btn.tagName.toLowerCase();
                        const classes = btn.className;
                        const href = btn.getAttribute('href');

                        if (text.length > 0 || href) {
                            found.push({
                                text,
                                type,
                                classes,
                                href: href || null
                            });
                        }
                    });

                    return found;
                });

                console.log(`   Found ${buttons.length} interactive elements:`);
                buttons.forEach((btn, idx) => {
                    if (idx < 10) { // Show first 10
                        console.log(`   - ${btn.text || btn.href} (${btn.type})`);
                    }
                });
                if (buttons.length > 10) {
                    console.log(`   ... and ${buttons.length - 10} more`);
                }

                navigationMap.modules.push({
                    name: module.name,
                    url: module.url,
                    buttons: buttons,
                    accessible: true
                });

            } catch (error) {
                console.log(`   ❌ Error accessing module: ${error.message}`);
                navigationMap.modules.push({
                    name: module.name,
                    url: module.url,
                    accessible: false,
                    error: error.message
                });
            }
        }

        // Save navigation map
        const outputPath = 'tests/e2e-screenshots/navigation-map.json';
        fs.writeFileSync(outputPath, JSON.stringify(navigationMap, null, 2));
        console.log(`\n✅ Navigation map saved to: ${outputPath}`);

    } catch (error) {
        console.error('❌ Discovery failed:', error.message);
    } finally {
        await browser.close();
    }
}

discoverNavigation().catch(console.error);
