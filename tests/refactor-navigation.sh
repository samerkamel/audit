#!/bin/bash
# Script to systematically refactor page.goto() calls to sidebar menu clicks
# This script will create patterns for each module

echo "Starting navigation refactor..."

# Backup original file
cp tests/comprehensive-e2e.cjs tests/comprehensive-e2e.cjs.backup

# Pattern replacements for each module
# Note: Login and first dashboard access should stay as page.goto

# Departments module navigation replacements
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/departments'"'"');|await clickSidebarMenuItem(page, '"'"'Departments'"'"');|g' tests/comprehensive-e2e.cjs
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/departments/create'"'"');|await clickSidebarMenuItem(page, '"'"'Departments'"'"'); await page.click('"'"'a[href*="/departments/create"]'"'"'); await page.waitForLoadState('"'"'networkidle'"'"'); await takeScreenshot(page, '"'"'department-create-form-loaded'"'"');|g' tests/comprehensive-e2e.cjs

# Users module navigation replacements
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/users'"'"');|await clickSidebarMenuItem(page, '"'"'User Management'"'"');|g' tests/comprehensive-e2e.cjs
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/users/create'"'"');|await clickSidebarMenuItem(page, '"'"'User Management'"'"'); await page.click('"'"'a[href*="/users/create"]'"'"'); await page.waitForLoadState('"'"'networkidle'"'"'); await takeScreenshot(page, '"'"'user-create-form-loaded'"'"');|g' tests/comprehensive-e2e.cjs

# External Audits module navigation replacements
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/external-audits'"'"');|await clickSidebarMenuItem(page, '"'"'External Audits'"'"');|g' tests/comprehensive-e2e.cjs
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/external-audits/create'"'"');|await clickSidebarMenuItem(page, '"'"'External Audits'"'"'); await page.click('"'"'a[href*="/external-audits/create"]'"'"'); await page.waitForLoadState('"'"'networkidle'"'"'); await takeScreenshot(page, '"'"'external-audit-create-form-loaded'"'"');|g' tests/comprehensive-e2e.cjs

# CARs module navigation replacements
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/cars'"'"');|await clickSidebarMenuItem(page, '"'"'CAR Management'"'"');|g' tests/comprehensive-e2e.cjs
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/cars/create'"'"');|await clickSidebarMenuItem(page, '"'"'CAR Management'"'"'); await page.click('"'"'a[href*="/cars/create"]'"'"'); await page.waitForLoadState('"'"'networkidle'"'"'); await takeScreenshot(page, '"'"'car-create-form-loaded'"'"');|g' tests/comprehensive-e2e.cjs

# Audit Plans module (remaining instances)
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/audit-plans'"'"');|await clickSidebarMenuItem(page, '"'"'Audit Plans'"'"');|g' tests/comprehensive-e2e.cjs
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/audit-plans/create'"'"');|await clickSidebarMenuItem(page, '"'"'Audit Plans'"'"'); await page.click('"'"'a[href*="/audit-plans/create"]'"'"'); await page.waitForLoadState('"'"'networkidle'"'"'); await takeScreenshot(page, '"'"'audit-plan-create-form-loaded'"'"');|g' tests/comprehensive-e2e.cjs

# Audit Questions module
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/audit-questions'"'"');|await clickSidebarMenuItem(page, '"'"'Audit Questions'"'"');|g' tests/comprehensive-e2e.cjs
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/audit-questions/create'"'"');|await clickSidebarMenuItem(page, '"'"'Audit Questions'"'"'); await page.click('"'"'a[href*="/audit-questions/create"]'"'"'); await page.waitForLoadState('"'"'networkidle'"'"'); await takeScreenshot(page, '"'"'audit-question-create-form-loaded'"'"');|g' tests/comprehensive-e2e.cjs

# CheckList Groups module (remaining instances)
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/checklist-groups'"'"');|await clickSidebarMenuItem(page, '"'"'CheckList Groups'"'"');|g' tests/comprehensive-e2e.cjs
sed -i.tmp 's|await page\.goto(config\.baseUrl + '"'"'/checklist-groups/create'"'"');|await clickSidebarMenuItem(page, '"'"'CheckList Groups'"'"'); await page.click('"'"'a[href*="/checklist-groups/create"]'"'"'); await page.waitForLoadState('"'"'networkidle'"'"'); await takeScreenshot(page, '"'"'checklist-group-create-form-loaded'"'"');|g' tests/comprehensive-e2e.cjs

# Clean up temp files
rm tests/comprehensive-e2e.cjs.tmp

echo "Refactoring complete! Backup saved as tests/comprehensive-e2e.cjs.backup"
echo "Review the changes and test the refactored file."
