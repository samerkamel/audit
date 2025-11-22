/**
 * Sidebar Menu Mapper
 * Manually clicks through sidebar menu to discover all navigation items
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

// Read base URL from .env
function getBaseUrlFromEnv() {
    const envPath = path.join(__dirname, '..', '.env');
    if (fs.existsSync(envPath)) {
        const envContent = fs.readFileSync(envPath, 'utf8');
        const match = envContent.match(/APP_URL=(.*)/);
        if (match) return match[1].trim();
    }
    return 'https://audit.test';
}

const config = {
    baseUrl: getBaseUrlFromEnv(),
    credentials: {
        email: 'admin@alfa-electronics.com',
        password: 'password'
    }
};

async function mapSidebar() {
    const browser = await chromium.launch({ headless: false });
    const context = await browser.newContext({ ignoreHTTPSErrors: true });
    const page = await context.newPage();

    console.log('🗺️ Mapping Sidebar Navigation...\n');

    try {
        // Login
        console.log('🔐 Logging in...');
        await page.goto(config.baseUrl + '/login');
        await page.waitForLoadState('networkidle');
        await page.fill('input[name="email"]', config.credentials.email);
        await page.fill('input[name="password"]', config.credentials.password);
        await page.click('button[type="submit"]');
        await page.waitForTimeout(5000);
        await page.waitForLoadState('networkidle');
        await page.goto(config.baseUrl + '/');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);
        console.log('✅ Login successful\n');

        // Take screenshot of sidebar
        await page.screenshot({ path: 'tests/e2e-screenshots/sidebar-structure.png', fullPage: true });
        console.log('📸 Screenshot saved: sidebar-structure.png\n');

        // Get all menu links by looking for actual visible text in sidebar
        const menuStructure = await page.evaluate(() => {
            const menu = [];

            // Find sidebar container - try multiple selectors
            const sidebarSelectors = [
                'aside.menu',
                '.layout-menu',
                'nav.menu',
                '.menu-vertical',
                '#layout-menu'
            ];

            let sidebar = null;
            for (const selector of sidebarSelectors) {
                sidebar = document.querySelector(selector);
                if (sidebar) break;
            }

            if (!sidebar) {
                return { error: 'Sidebar not found', tried: sidebarSelectors };
            }

            // Get all links in sidebar
            const links = sidebar.querySelectorAll('a');
            links.forEach((link, idx) => {
                const text = link.textContent?.trim().replace(/\s+/g, ' ');
                const href = link.getAttribute('href');
                const classes = link.className;
                const isVisible = link.offsetParent !== null;

                // Skip empty links and language switcher
                if (text && text.length > 2 && !text.includes('Language') && !text.includes('Search')) {
                    menu.push({
                        index: idx,
                        text,
                        href,
                        classes,
                        isVisible,
                        hasSubmenu: link.parentElement?.querySelector('.menu-sub') !== null
                    });
                }
            });

            return { menu, sidebarFound: true };
        });

        if (menuStructure.error) {
            console.log(`❌ ${menuStructure.error}`);
            console.log(`   Tried selectors: ${menuStructure.tried.join(', ')}\n`);

            // Try to find any menu-like elements
            console.log('🔍 Searching for menu elements...');
            const elements = await page.evaluate(() => {
                return Array.from(document.querySelectorAll('[class*="menu"], [class*="sidebar"], [class*="nav"]'))
                    .map(el => ({ tag: el.tagName, class: el.className, id: el.id }))
                    .slice(0, 10);
            });
            console.log('Found elements:', JSON.stringify(elements, null, 2));
        } else {
            console.log('📋 SIDEBAR MENU ITEMS:\n');
            menuStructure.menu.forEach(item => {
                console.log(`${item.index + 1}. ${item.text}`);
                console.log(`   URL: ${item.href}`);
                console.log(`   Visible: ${item.isVisible}`);
                console.log(`   Has Submenu: ${item.hasSubmenu}\n`);
            });

            // Save to JSON
            fs.writeFileSync(
                'tests/e2e-screenshots/sidebar-menu.json',
                JSON.stringify(menuStructure.menu, null, 2)
            );
            console.log(`✅ Menu structure saved to: sidebar-menu.json\n`);
            console.log(`📊 Total menu items found: ${menuStructure.menu.length}`);
        }

    } catch (error) {
        console.error('❌ Error:', error.message);
    } finally {
        await browser.close();
    }
}

mapSidebar().catch(console.error);
