import fs from 'node:fs/promises'
import path from 'node:path'
import { chromium } from 'playwright'

const baseUrl = process.env.README_APP_URL ?? 'http://127.0.0.1:8000'
const outputDir = path.resolve(process.cwd(), 'docs/screenshots')

const demoUser = {
  email: 'demo@dinojelly.local',
  password: 'password',
}

const adminUser = {
  email: 'admin@dinojelly.local',
  password: 'password',
}

async function ensureDir(dir) {
  await fs.mkdir(dir, { recursive: true })
}

async function waitForStableUi(page) {
  await page.waitForLoadState('networkidle')
  await page.waitForTimeout(1200)
}

async function login(page, credentials, destination = '/') {
  await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' })
  await page.locator('input[type="email"]').fill(credentials.email)
  await page.locator('input[type="password"]').fill(credentials.password)
  await Promise.all([
    page.waitForURL((url) => !url.pathname.endsWith('/login')),
    page.locator('form.auth-form').getByRole('button').click(),
  ])
  await page.goto(`${baseUrl}${destination}`, { waitUntil: 'domcontentloaded' })
  await waitForStableUi(page)
}

async function captureStorefront(browser) {
  const context = await browser.newContext({
    viewport: { width: 1440, height: 1600 },
    deviceScaleFactor: 1.5,
  })
  const page = await context.newPage()

  await page.goto(baseUrl, { waitUntil: 'domcontentloaded' })
  await waitForStableUi(page)
  await page.screenshot({
    path: path.join(outputDir, 'storefront-catalog.png'),
    fullPage: false,
  })

  await page.locator('.product-card').first().click()
  await page.waitForURL(/\/products\/\d+$/)
  await waitForStableUi(page)
  await page.screenshot({
    path: path.join(outputDir, 'storefront-product.png'),
    fullPage: false,
  })

  await login(page, demoUser)
  await page.goto(baseUrl, { waitUntil: 'domcontentloaded' })
  await waitForStableUi(page)
  await page.locator('.product-card__button').first().click()
  await page.waitForTimeout(1000)

  await page.goto(`${baseUrl}/cart`, { waitUntil: 'domcontentloaded' })
  await waitForStableUi(page)
  await page.screenshot({
    path: path.join(outputDir, 'storefront-cart.png'),
    fullPage: false,
  })

  await page.goto(`${baseUrl}/checkout`, { waitUntil: 'domcontentloaded' })
  await waitForStableUi(page)
  await page.screenshot({
    path: path.join(outputDir, 'storefront-checkout.png'),
    fullPage: false,
  })

  await context.close()
}

async function captureAdmin(browser) {
  const context = await browser.newContext({
    viewport: { width: 1440, height: 1500 },
    deviceScaleFactor: 1.5,
  })
  const page = await context.newPage()

  await login(page, adminUser, '/admin')
  await page.screenshot({
    path: path.join(outputDir, 'admin-dashboard.png'),
    fullPage: false,
  })

  await context.close()
}

async function main() {
  await ensureDir(outputDir)

  const browser = await chromium.launch({ headless: true })

  try {
    await captureStorefront(browser)
    await captureAdmin(browser)
  } finally {
    await browser.close()
  }
}

main().catch((error) => {
  console.error(error)
  process.exitCode = 1
})
