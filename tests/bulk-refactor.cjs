#!/usr/bin/env node
/**
 * Bulk Navigation Refactoring Script
 * Replaces all page.goto() calls with sidebar menu navigation
 */

const fs = require('fs');
const path = require('path');

const testFile = path.join(__dirname, 'comprehensive-e2e.cjs');
let content = fs.readFileSync(testFile, 'utf8');

// Backup original
fs.writeFileSync(testFile + '.pre-bulk-refactor', content);

console.log('🔄 Starting bulk refactoring...\n');

// Track replacements
const replacements = [];

// Define replacement patterns for each module
const patterns = [
    // Departments module
    {
        module: 'Departments',
        find: /await page\.goto\(config\.baseUrl \+ '\/departments\/create'\);/g,
        replace: `await clickSidebarMenuItem(page, 'Departments');\n        await page.click('a[href*="/departments/create"]');\n        await page.waitForLoadState('networkidle');\n        await takeScreenshot(page, 'department-form-loaded');`,
        desc: 'Department create navigation'
    },
    {
        find: /await page\.goto\(config\.baseUrl \+ '\/departments'\);/g,
        replace: `await clickSidebarMenuItem(page, 'Departments');`,
        desc: 'Department list navigation'
    },

    // Users module
    {
        module: 'Users',
        find: /await page\.goto\(config\.baseUrl \+ '\/users\/create'\);/g,
        replace: `await clickSidebarMenuItem(page, 'User Management');\n        await page.click('a[href*="/users/create"]');\n        await page.waitForLoadState('networkidle');\n        await takeScreenshot(page, 'user-form-loaded');`,
        desc: 'User create navigation'
    },
    {
        find: /await page\.goto\(config\.baseUrl \+ '\/users'\);/g,
        replace: `await clickSidebarMenuItem(page, 'User Management');`,
        desc: 'User list navigation'
    },

    // External Audits module
    {
        module: 'External Audits',
        find: /await page\.goto\(config\.baseUrl \+ '\/external-audits\/create'\);/g,
        replace: `await clickSidebarMenuItem(page, 'External Audits');\n        await page.click('a[href*="/external-audits/create"]');\n        await page.waitForLoadState('networkidle');\n        await takeScreenshot(page, 'external-audit-form-loaded');`,
        desc: 'External Audit create navigation'
    },
    {
        find: /await page\.goto\(config\.baseUrl \+ '\/external-audits'\);/g,
        replace: `await clickSidebarMenuItem(page, 'External Audits');`,
        desc: 'External Audit list navigation'
    },

    // CARs module
    {
        module: 'CARs',
        find: /await page\.goto\(config\.baseUrl \+ '\/cars\/create'\);/g,
        replace: `await clickSidebarMenuItem(page, 'CAR Management');\n        await page.click('a[href*="/cars/create"]');\n        await page.waitForLoadState('networkidle');\n        await takeScreenshot(page, 'car-form-loaded');`,
        desc: 'CAR create navigation'
    },
    {
        find: /await page\.goto\(config\.baseUrl \+ '\/cars'\);/g,
        replace: `await clickSidebarMenuItem(page, 'CAR Management');`,
        desc: 'CAR list navigation'
    },

    // Audit Plans module (remaining)
    {
        module: 'Audit Plans',
        find: /await page\.goto\(config\.baseUrl \+ '\/audit-plans\/create'\);/g,
        replace: `await clickSidebarMenuItem(page, 'Audit Plans');\n        await page.click('a[href*="/audit-plans/create"]');\n        await page.waitForLoadState('networkidle');\n        await takeScreenshot(page, 'audit-plan-form-loaded');`,
        desc: 'Audit Plan create navigation'
    },
    {
        find: /await page\.goto\(config\.baseUrl \+ '\/audit-plans'\);/g,
        replace: `await clickSidebarMenuItem(page, 'Audit Plans');`,
        desc: 'Audit Plan list navigation'
    },

    // Audit Questions module
    {
        module: 'Audit Questions',
        find: /await page\.goto\(config\.baseUrl \+ '\/audit-questions\/create'\);/g,
        replace: `await clickSidebarMenuItem(page, 'Audit Questions');\n        await page.click('a[href*="/audit-questions/create"]');\n        await page.waitForLoadState('networkidle');\n        await takeScreenshot(page, 'audit-question-form-loaded');`,
        desc: 'Audit Question create navigation'
    },
    {
        find: /await page\.goto\(config\.baseUrl \+ '\/audit-questions'\);/g,
        replace: `await clickSidebarMenuItem(page, 'Audit Questions');`,
        desc: 'Audit Question list navigation'
    },

    // CheckList Groups module (remaining)
    {
        module: 'CheckList Groups',
        find: /await page\.goto\(config\.baseUrl \+ '\/checklist-groups\/create'\);/g,
        replace: `await clickSidebarMenuItem(page, 'CheckList Groups');\n        await page.click('a[href*="/checklist-groups/create"]');\n        await page.waitForLoadState('networkidle');\n        await takeScreenshot(page, 'checklist-group-form-loaded');`,
        desc: 'CheckList Group create navigation'
    },
    {
        find: /await page\.goto\(config\.baseUrl \+ '\/checklist-groups'\);/g,
        replace: `await clickSidebarMenuItem(page, 'CheckList Groups');`,
        desc: 'CheckList Group list navigation'
    }
];

// Apply all replacements
patterns.forEach(pattern => {
    const before = content;
    content = content.replace(pattern.find, pattern.replace);
    const count = (before.match(pattern.find) || []).length;
    if (count > 0) {
        replacements.push({ desc: pattern.desc, count, module: pattern.module });
        console.log(`✅ ${pattern.desc}: ${count} replacement(s)`);
    }
});

// Write refactored content
fs.writeFileSync(testFile, content);

console.log('\n📊 Refactoring Summary:');
console.log(`Total patterns replaced: ${replacements.length}`);
console.log(`Total replacements made: ${replacements.reduce((sum, r) => sum + r.count, 0)}`);

console.log('\n✨ Refactoring complete!');
console.log(`Original backed up to: ${testFile}.pre-bulk-refactor`);
