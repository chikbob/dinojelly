# -*- coding: utf-8 -*-
from __future__ import annotations

from pathlib import Path
import math

from PIL import Image, ImageDraw, ImageFont


ROOT = Path("/Users/chikbob/Projects/dinojelly-new")
DIAGRAMS = ROOT / "thesis_assets" / "diagrams"
SCREENSHOTS = ROOT / "thesis_assets" / "screenshots"


def get_font(size: int, bold: bool = False):
    candidates = [
        "/System/Library/Fonts/Supplemental/Arial Bold.ttf" if bold else "/System/Library/Fonts/Supplemental/Arial.ttf",
        "/Library/Fonts/Arial Bold.ttf" if bold else "/Library/Fonts/Arial.ttf",
        "/System/Library/Fonts/Supplemental/Helvetica.ttc",
    ]
    for path in candidates:
        if Path(path).exists():
            return ImageFont.truetype(path, size)
    return ImageFont.load_default()


FONT_18 = get_font(18)
FONT_20 = get_font(20)
FONT_22 = get_font(22)
FONT_24 = get_font(24, bold=True)
FONT_26 = get_font(26, bold=True)
FONT_30 = get_font(30, bold=True)

BG = (255, 255, 255)
TEXT = (17, 24, 39)
LINE = (71, 85, 105)
MUTED = (100, 116, 139)
BLUE = (239, 246, 255)
GREEN = (236, 253, 245)
ROSE = (255, 241, 242)
SAND = (255, 251, 235)


def draw_box(draw: ImageDraw.ImageDraw, xy, title: str, lines: list[str], fill=BLUE, line_font=None):
    x1, y1, x2, y2 = xy
    draw.rounded_rectangle(xy, radius=18, fill=fill, outline=LINE, width=3)
    draw.text((x1 + 18, y1 + 14), title, font=FONT_26, fill=TEXT)
    draw.line((x1 + 16, y1 + 52, x2 - 16, y1 + 52), fill=LINE, width=2)
    y = y1 + 68
    line_font = line_font or FONT_20
    for line in lines:
        draw.text((x1 + 18, y), line, font=line_font, fill=TEXT)
        y += 30


def centered_text(draw: ImageDraw.ImageDraw, xy, text: str, font, fill=TEXT):
    x1, y1, x2, y2 = xy
    bb = draw.multiline_textbbox((0, 0), text, font=font, spacing=4)
    draw.multiline_text(
        (x1 + (x2 - x1 - (bb[2] - bb[0])) / 2, y1 + (y2 - y1 - (bb[3] - bb[1])) / 2),
        text,
        font=font,
        fill=fill,
        align="center",
        spacing=4,
    )


def draw_deployment_node(draw: ImageDraw.ImageDraw, xy, title: str, stereotype: str, lines: list[str], fill=BLUE):
    x1, y1, x2, y2 = xy
    depth = 26
    draw.polygon(
        [(x1 + depth, y1 - depth), (x2 + depth, y1 - depth), (x2, y1), (x1, y1)],
        fill=(248, 250, 252),
        outline=LINE,
    )
    draw.polygon(
        [(x2, y1), (x2 + depth, y1 - depth), (x2 + depth, y2 - depth), (x2, y2)],
        fill=(226, 232, 240),
        outline=LINE,
    )
    draw.rounded_rectangle(xy, radius=8, fill=fill, outline=LINE, width=3)
    draw.text((x1 + 18, y1 + 12), f"«{stereotype}»", font=FONT_18, fill=MUTED)
    draw.text((x1 + 18, y1 + 42), title, font=FONT_24, fill=TEXT)
    draw.line((x1 + 16, y1 + 82, x2 - 16, y1 + 82), fill=LINE, width=2)
    y = y1 + 98
    for line in lines:
        draw.text((x1 + 18, y), line, font=FONT_20, fill=TEXT)
        y += 30


def draw_artifact(draw: ImageDraw.ImageDraw, xy, title: str, lines: list[str], fill=BG):
    x1, y1, x2, y2 = xy
    draw.rounded_rectangle(xy, radius=6, fill=fill, outline=LINE, width=2)
    tab = [(x2 - 70, y1), (x2 - 30, y1), (x2 - 16, y1 + 14), (x2 - 70, y1 + 14)]
    draw.polygon(tab, fill=(248, 250, 252), outline=LINE)
    draw.text((x1 + 12, y1 + 10), "«artifact»", font=FONT_18, fill=MUTED)
    draw.text((x1 + 12, y1 + 40), title, font=FONT_22, fill=TEXT)
    y = y1 + 78
    for line in lines:
        draw.text((x1 + 14, y), line, font=FONT_18, fill=TEXT)
        y += 26


def draw_component_box(draw: ImageDraw.ImageDraw, xy, title: str, lines: list[str], fill=BLUE):
    x1, y1, x2, y2 = xy
    draw.rounded_rectangle(xy, radius=6, fill=fill, outline=LINE, width=3)
    icon_x = x2 - 70
    icon_y = y1 + 22
    draw.rectangle((icon_x, icon_y, icon_x + 42, icon_y + 34), fill=BG, outline=LINE, width=2)
    draw.rectangle((icon_x - 14, icon_y + 6, icon_x + 6, icon_y + 16), fill=BG, outline=LINE, width=2)
    draw.rectangle((icon_x - 14, icon_y + 22, icon_x + 6, icon_y + 32), fill=BG, outline=LINE, width=2)
    draw.text((x1 + 18, y1 + 12), "«component»", font=FONT_18, fill=MUTED)
    draw.text((x1 + 18, y1 + 42), title, font=FONT_24, fill=TEXT)
    draw.line((x1 + 16, y1 + 82, x2 - 16, y1 + 82), fill=LINE, width=2)
    y = y1 + 102
    for line in lines:
        draw.text((x1 + 18, y), line, font=FONT_20, fill=TEXT)
        y += 30


def draw_interface(draw: ImageDraw.ImageDraw, center: tuple[int, int], name: str, side: str = "right"):
    x, y = center
    r = 15
    draw.ellipse((x - r, y - r, x + r, y + r), fill=BG, outline=LINE, width=3)
    tx = x + 24 if side == "right" else x - 24 - draw.textbbox((0, 0), name, font=FONT_18)[2]
    draw.text((tx, y - 11), name, font=FONT_18, fill=TEXT)


def draw_dashed_arrow(draw: ImageDraw.ImageDraw, start, end, label: str | None = None):
    x1, y1 = start
    x2, y2 = end
    length = math.hypot(x2 - x1, y2 - y1)
    if length == 0:
        return
    dx = (x2 - x1) / length
    dy = (y2 - y1) / length
    dash = 18
    gap = 10
    pos = 0
    while pos < length - 14:
        sx = x1 + dx * pos
        sy = y1 + dy * pos
        ex = x1 + dx * min(pos + dash, length - 14)
        ey = y1 + dy * min(pos + dash, length - 14)
        draw.line((sx, sy, ex, ey), fill=LINE, width=3)
        pos += dash + gap
    angle = math.atan2(y2 - y1, x2 - x1)
    head = 14
    for delta in (2.5, -2.5):
        hx = x2 - head * math.cos(angle + delta)
        hy = y2 - head * math.sin(angle + delta)
        draw.line((x2, y2, hx, hy), fill=LINE, width=3)
    if label:
        lx = (x1 + x2) / 2
        ly = (y1 + y2) / 2 - 22
        bb = draw.textbbox((0, 0), label, font=FONT_18)
        draw.rounded_rectangle((lx - (bb[2] - bb[0]) / 2 - 8, ly - 4, lx + (bb[2] - bb[0]) / 2 + 8, ly + 24), radius=8, fill=BG)
        draw.text((lx - (bb[2] - bb[0]) / 2, ly), label, font=FONT_18, fill=TEXT)


def draw_actor(draw: ImageDraw.ImageDraw, x: int, y: int, label: str):
    draw.ellipse((x - 24, y, x + 24, y + 48), outline=LINE, width=3, fill=BG)
    draw.line((x, y + 48, x, y + 128), fill=LINE, width=3)
    draw.line((x - 38, y + 78, x + 38, y + 78), fill=LINE, width=3)
    draw.line((x, y + 128, x - 34, y + 176), fill=LINE, width=3)
    draw.line((x, y + 128, x + 34, y + 176), fill=LINE, width=3)
    bbox = draw.multiline_textbbox((0, 0), label, font=FONT_20, spacing=4)
    draw.multiline_text((x - (bbox[2] - bbox[0]) / 2, y + 192), label, font=FONT_20, fill=TEXT, align="center", spacing=4)


def draw_arrow(draw: ImageDraw.ImageDraw, start, end, label: str | None = None, elbow: tuple[int, int] | None = None):
    points = [start]
    if elbow:
        points.extend([(elbow[0], start[1]), elbow, (end[0], elbow[1])])
    points.append(end)
    draw.line(points, fill=LINE, width=3)
    sx, sy = points[-2]
    ex, ey = end
    angle = math.atan2(ey - sy, ex - sx)
    head = 12
    for delta in (2.5, -2.5):
        hx = ex - head * math.cos(angle + delta)
        hy = ey - head * math.sin(angle + delta)
        draw.line((ex, ey, hx, hy), fill=LINE, width=3)
    if label:
        lx = (start[0] + end[0]) / 2
        ly = (start[1] + end[1]) / 2 - 16
        bb = draw.textbbox((0, 0), label, font=FONT_18)
        draw.rounded_rectangle((lx - (bb[2] - bb[0]) / 2 - 8, ly - 4, lx + (bb[2] - bb[0]) / 2 + 8, ly + 24), radius=8, fill=BG)
        draw.text((lx - (bb[2] - bb[0]) / 2, ly), label, font=FONT_18, fill=TEXT)


def draw_polyline_arrow(draw: ImageDraw.ImageDraw, points: list[tuple[int, int]], label: str | None = None):
    draw.line(points, fill=LINE, width=3)
    sx, sy = points[-2]
    ex, ey = points[-1]
    angle = math.atan2(ey - sy, ex - sx)
    head = 12
    for delta in (2.5, -2.5):
        hx = ex - head * math.cos(angle + delta)
        hy = ey - head * math.sin(angle + delta)
        draw.line((ex, ey, hx, hy), fill=LINE, width=3)
    if label:
        mid = len(points) // 2
        lx, ly = points[mid]
        bb = draw.textbbox((0, 0), label, font=FONT_18)
        draw.rounded_rectangle((lx - (bb[2] - bb[0]) / 2 - 8, ly - 18, lx + (bb[2] - bb[0]) / 2 + 8, ly + 10), radius=8, fill=BG)
        draw.text((lx - (bb[2] - bb[0]) / 2, ly - 14), label, font=FONT_18, fill=TEXT)


def draw_link(draw: ImageDraw.ImageDraw, start, end, elbow: tuple[int, int] | None = None):
    points = [start]
    if elbow:
        points.extend([(elbow[0], start[1]), elbow, (end[0], elbow[1])])
    points.append(end)
    draw.line(points, fill=LINE, width=3)


def draw_polyline_link(draw: ImageDraw.ImageDraw, points: list[tuple[int, int]]):
    draw.line(points, fill=LINE, width=3)


def architecture():
    img = Image.new("RGB", (2600, 1500), BG)
    draw = ImageDraw.Draw(img)
    draw.text((690, 26), "UML Deployment Diagram: загальна архітектура DinoJelly", font=FONT_30, fill=TEXT)

    draw_deployment_node(
        draw,
        (90, 185, 470, 390),
        "Browser",
        "device",
        ["Покупець / адміністратор", "HTML, CSS, JavaScript", "HTTPS-клієнт"],
        BLUE,
    )
    draw_artifact(draw, (130, 450, 430, 590), "Vue/Inertia UI", ["resources/pages", "resources/components"], BG)

    draw_deployment_node(
        draw,
        (640, 125, 1230, 760),
        "Web application server",
        "executionEnvironment",
        ["Docker container", "Nginx", "PHP-FPM 8.1", "Laravel 10 runtime"],
        BLUE,
    )
    draw_artifact(draw, (700, 380, 1170, 540), "Laravel application", ["routes/web.php", "controllers", "middleware"], BG)
    draw_artifact(draw, (700, 570, 1170, 705), "Domain services", ["CheckoutService, OrderService", "PaymentService, InventoryService"], BG)

    draw_deployment_node(
        draw,
        (1450, 130, 1950, 380),
        "MySQL server",
        "database",
        ["users, products", "orders, payments", "stock, referrals"],
        GREEN,
    )
    draw_deployment_node(
        draw,
        (1450, 510, 1950, 730),
        "Redis server",
        "node",
        ["session store", "cache store", "queue backend"],
        GREEN,
    )
    draw_deployment_node(
        draw,
        (1450, 860, 1950, 1080),
        "Mail service",
        "node",
        ["SMTP transport", "abandoned cart emails", "system notifications"],
        SAND,
    )
    draw_deployment_node(
        draw,
        (2100, 170, 2500, 390),
        "Mock payment provider",
        "externalSystem",
        ["payment page", "payment webhook", "status callback"],
        ROSE,
    )
    draw_deployment_node(
        draw,
        (2100, 610, 2500, 870),
        "CI/CD environment",
        "node",
        ["GitHub Actions", "build / test", "deploy manifests"],
        ROSE,
    )

    draw_arrow(draw, (470, 285), (640, 285), "HTTPS")
    draw_link(draw, (280, 390), (280, 450))
    draw_arrow(draw, (1230, 245), (1450, 245), "SQL/TCP")
    draw_arrow(draw, (1230, 610), (1450, 610), "Redis protocol")
    draw_polyline_arrow(draw, [(1080, 705), (1080, 970), (1450, 970)], "SMTP")
    draw_polyline_arrow(draw, [(1230, 190), (1340, 95), (2060, 95), (2100, 235)], "HTTPS redirect")
    draw_polyline_arrow(draw, [(2100, 330), (2020, 330), (2020, 470), (1230, 470)], "webhook HTTP")
    draw_polyline_arrow(draw, [(2100, 740), (1340, 740), (1340, 710), (1230, 710)], "deploy")

    centered_text(
        draw,
        (80, 1260, 2520, 1335),
        "Рисунок 2.1 – Загальна архітектура веборієнтованої інформаційної системи DinoJelly",
        FONT_24,
    )

    img.save(DIAGRAMS / "01_architecture.png")
    img.save(DIAGRAMS / "figure_2_1_architecture.png")


def er_diagram():
    img = Image.new("RGB", (3000, 1880), BG)
    draw = ImageDraw.Draw(img)
    draw.text((1120, 26), "ER-диаграмма ключевых сущностей", font=FONT_30, fill=TEXT)

    boxes = {
        "users": ((90, 140, 540, 420), "users", ["PK  id", "name", "email", "role", "referral_code"]),
        "addresses": ((90, 560, 540, 850), "addresses", ["PK  id", "FK  user_id", "recipient_name", "phone", "is_default"]),
        "referrals": ((90, 980, 540, 1290), "referrals", ["PK  id", "FK  referrer_user_id", "FK  referred_user_id", "FK  order_id", "status"]),
        "categories": ((730, 140, 1180, 400), "categories", ["PK  id", "name", "slug", "is_active"]),
        "products": ((730, 520, 1180, 860), "products", ["PK  id", "FK  category_id", "name", "price", "old_price", "image"]),
        "stock_items": ((730, 980, 1180, 1280), "stock_items", ["PK  id", "FK  product_id", "sku", "quantity", "reserved_quantity"]),
        "orders": ((1380, 140, 1880, 500), "orders", ["PK  id", "FK  user_id", "FK  address_id", "FK  delivery_slot_id", "FK  subscription_id", "FK  gift_card_id", "status"]),
        "order_items": ((1380, 640, 1880, 940), "order_items", ["PK  id", "FK  order_id", "FK  product_id", "quantity", "price"]),
        "payments": ((1380, 1080, 1880, 1360), "payments", ["PK  id", "FK  order_id", "provider", "provider_payment_id", "status"]),
        "subscriptions": ((2100, 140, 2600, 470), "subscriptions", ["PK  id", "FK  user_id", "FK  address_id", "FK  delivery_slot_id", "FK  source_order_id", "interval_days"]),
        "subscription_items": ((2100, 620, 2600, 920), "subscription_items", ["PK  id", "FK  subscription_id", "FK  product_id", "quantity", "price"]),
        "gift_cards": ((2100, 1080, 2600, 1400), "gift_cards", ["PK  id", "FK  purchaser_user_id", "FK  recipient_user_id", "FK  order_id", "code", "balance"]),
    }

    for xy, title, lines in boxes.values():
        draw_box(draw, xy, title, lines, BLUE, line_font=FONT_18)

    def center_right(key):
        x1, y1, x2, y2 = boxes[key][0]
        return (x2, (y1 + y2) // 2)

    def center_left(key):
        x1, y1, x2, y2 = boxes[key][0]
        return (x1, (y1 + y2) // 2)

    def center_top(key):
        x1, y1, x2, y2 = boxes[key][0]
        return ((x1 + x2) // 2, y1)

    def center_bottom(key):
        x1, y1, x2, y2 = boxes[key][0]
        return ((x1 + x2) // 2, y2)

    draw_arrow(draw, center_bottom("users"), center_top("addresses"), "1:M")
    draw_polyline_arrow(draw, [center_right("users"), (650, 280), center_left("orders")], "1:M")
    draw_arrow(draw, center_bottom("users"), center_top("referrals"), "1:M")
    draw_arrow(draw, center_bottom("categories"), center_top("products"), "1:M")
    draw_arrow(draw, center_bottom("products"), center_top("stock_items"), "1:1")
    draw_polyline_arrow(draw, [center_right("products"), (1280, 690), center_left("order_items")], "1:M")
    draw_arrow(draw, center_bottom("orders"), center_top("order_items"), "1:M")
    draw_arrow(draw, center_bottom("orders"), center_top("payments"), "1:M")
    draw_polyline_arrow(draw, [center_right("orders"), (1980, 320), center_left("subscriptions")], "1:M")
    draw_arrow(draw, center_bottom("subscriptions"), center_top("subscription_items"), "1:M")
    draw_polyline_arrow(draw, [center_right("products"), (1960, 760), center_left("subscription_items")], "1:M")
    draw_arrow(draw, center_bottom("subscriptions"), center_top("gift_cards"), "1:M")
    draw_polyline_arrow(draw, [center_bottom("users"), (315, 1520), (1980, 1520), (1980, 1240), center_left("gift_cards")], "1:M")
    draw_polyline_arrow(draw, [center_right("referrals"), (640, 1135), (640, 1470), (1320, 1470), (1320, 320), center_left("orders")], "M:1")

    img.save(DIAGRAMS / "02_er_diagram.png")


def use_case():
    img = Image.new("RGB", (2400, 1480), BG)
    draw = ImageDraw.Draw(img)
    draw.text((920, 26), "Диаграмма вариантов использования", font=FONT_30, fill=TEXT)
    draw.rounded_rectangle((430, 110, 1970, 1320), radius=26, outline=LINE, width=4, fill=(248, 250, 252))
    draw.text((1080, 130), "Система DinoJelly", font=FONT_26, fill=TEXT)

    draw_actor(draw, 170, 260, "Покупатель")
    draw_actor(draw, 170, 880, "Администратор")
    draw_actor(draw, 2210, 330, "Планировщик\nзадач")
    draw_actor(draw, 2210, 900, "Платежный\nпровайдер\n(mock)")

    use_cases = {
        "catalog": (650, 220, 1100, 320, "Просмотр каталога"),
        "assistant": (1260, 220, 1710, 320, "AI-подбор товаров"),
        "cart": (650, 390, 1100, 490, "Управление корзиной"),
        "checkout": (650, 560, 1100, 660, "Оформление заказа"),
        "orders": (650, 730, 1100, 850, "Просмотр заказа и\nповторная покупка"),
        "subs": (650, 930, 1100, 1030, "Управление подпиской"),
        "catalog_admin": (1260, 470, 1710, 590, "Управление товарами,\nкатегориями и складом"),
        "orders_admin": (1260, 670, 1710, 790, "Обработка заказов,\nплатежей и отзывов"),
        "analytics": (1260, 900, 1710, 1000, "Просмотр аналитики"),
        "recovery": (1260, 1100, 1710, 1200, "Восстановление\nброшенной корзины"),
    }
    for _, (x1, y1, x2, y2, label) in use_cases.items():
        draw.rounded_rectangle((x1, y1, x2, y2), radius=40, outline=LINE, width=3, fill=BG)
        bb = draw.multiline_textbbox((0, 0), label, font=FONT_22, spacing=4)
        draw.multiline_text((x1 + (x2 - x1 - (bb[2] - bb[0])) / 2, y1 + (y2 - y1 - (bb[3] - bb[1])) / 2), label, font=FONT_22, fill=TEXT, align="center", spacing=4)

    buyer_starts = [(250, 350), (250, 390), (250, 430), (250, 470), (250, 510), (250, 550)]
    buyer_ends = [(650, 270), (1260, 270), (650, 440), (650, 610), (650, 790), (650, 980)]
    for start, end in zip(buyer_starts, buyer_ends):
        draw_link(draw, start, end)

    draw_polyline_link(draw, [(250, 980), (520, 980), (520, 530), (1260, 530)])
    draw_polyline_link(draw, [(250, 1030), (560, 1030), (560, 730), (1260, 730)])
    draw_polyline_link(draw, [(250, 1080), (610, 1080), (610, 950), (1260, 950)])
    draw_polyline_link(draw, [(2145, 515), (1960, 515), (1960, 1150), (1710, 1150)])
    draw_polyline_link(draw, [(2145, 1000), (1960, 1000), (1960, 730), (1710, 730)])

    img.save(DIAGRAMS / "03_use_case.png")


def sequence():
    img = Image.new("RGB", (2700, 1550), BG)
    draw = ImageDraw.Draw(img)
    draw.text((980, 26), "Sequence-диаграмма оформления заказа", font=FONT_30, fill=TEXT)

    labels = ["Покупатель", "Vue / Inertia", "OrderController", "CheckoutService", "PaymentService", "Webhook", "MySQL"]
    xs = [140, 470, 820, 1170, 1540, 1890, 2250]
    for x, label in zip(xs, labels):
        bb = draw.textbbox((0, 0), label, font=FONT_22)
        draw.rounded_rectangle((x - 90, 90, x + 90, 140), radius=12, fill=BLUE, outline=LINE, width=2)
        draw.text((x - (bb[2] - bb[0]) / 2, 104), label, font=FONT_22, fill=TEXT)
        draw.line((x, 140, x, 1460), fill=MUTED, width=2)

    steps = [
        (0, 1, 200, "Открыть /checkout"),
        (1, 2, 280, "GET /checkout"),
        (2, 3, 360, "getCheckoutPage(user)"),
        (3, 6, 440, "SELECT cart, addresses, slots"),
        (6, 3, 520, "данные checkout"),
        (3, 2, 600, "payload"),
        (2, 1, 680, "Inertia props"),
        (0, 1, 800, "Подтвердить заказ"),
        (1, 2, 880, "POST /checkout"),
        (2, 3, 960, "createOrder(...)"),
        (3, 6, 1040, "INSERT orders + order_items"),
        (3, 4, 1120, "createForOrder(order)"),
        (4, 6, 1200, "INSERT payments"),
        (6, 4, 1280, "payment row"),
    ]
    for a, b, y, label in steps:
        draw_arrow(draw, (xs[a], y), (xs[b], y), label)
    draw_arrow(draw, (1890, 540), (1540, 540), "POST webhook")
    draw_arrow(draw, (1890, 620), (1540, 620), "status: paid / failed")
    draw_arrow(draw, (1540, 700), (2250, 700), "UPDATE payments, orders")
    draw_arrow(draw, (1170, 780), (2250, 780), "reserve stock")
    draw_arrow(draw, (1540, 860), (1170, 860), "commit / release")
    note_xy = (1760, 1020, 2520, 1210)
    draw.rounded_rectangle(note_xy, radius=14, fill=(248, 250, 252), outline=LINE, width=2)
    draw.multiline_text((1790, 1050), "После mock-оплаты приложение\nполучает webhook и завершает\nлибо откатывает транзакционный\nсценарий заказа.", font=FONT_22, fill=TEXT, spacing=6)

    img.save(DIAGRAMS / "04_sequence.png")


def component():
    img = Image.new("RGB", (2600, 1450), BG)
    draw = ImageDraw.Draw(img)
    draw.text((890, 26), "UML Component Diagram: програмна система DinoJelly", font=FONT_30, fill=TEXT)

    draw_component_box(
        draw,
        (80, 190, 470, 420),
        "Client UI",
        ["Vue pages", "Vue components", "Inertia client"],
        BLUE,
    )

    draw_component_box(
        draw,
        (610, 190, 1010, 420),
        "Routing Layer",
        ["routes/web.php", "auth/admin middleware", "CSRF protection"],
        BLUE,
    )

    draw_component_box(
        draw,
        (1150, 170, 1570, 440),
        "Controllers",
        ["Storefront controllers", "Admin controllers", "HTTP request validation"],
        ROSE,
    )

    draw_component_box(
        draw,
        (1710, 150, 2140, 460),
        "Domain Services",
        ["CatalogService", "CheckoutService", "OrderService", "PaymentService", "InventoryService"],
        GREEN,
    )

    draw_component_box(
        draw,
        (2250, 190, 2570, 420),
        "Persistence Model",
        ["Eloquent models", "API resources", "migrations"],
        BLUE,
    )

    draw_component_box(
        draw,
        (80, 780, 470, 1010),
        "Jobs and Commands",
        ["ProcessDueSubscriptions", "SendCartRecovery", "queued mail job"],
        SAND,
    )

    draw_component_box(
        draw,
        (610, 760, 1010, 1040),
        "Marketing Services",
        ["ReferralService", "GiftCardService", "RecommendationAssistantService", "AbandonedCartService"],
        SAND,
    )

    draw_component_box(
        draw,
        (1150, 760, 1570, 1040),
        "Infrastructure Adapters",
        ["DB connection", "Redis queue/cache", "Mail transport", "Payment webhook"],
        ROSE,
    )

    draw_component_box(
        draw,
        (1710, 760, 2140, 1040),
        "External Services",
        ["MySQL", "Redis", "Mail", "Mock payment provider"],
        GREEN,
    )

    draw_dashed_arrow(draw, (470, 305), (610, 305), "uses")
    draw_dashed_arrow(draw, (1010, 305), (1150, 305), "uses")
    draw_dashed_arrow(draw, (1570, 305), (1710, 305), "uses")
    draw_dashed_arrow(draw, (2140, 305), (2250, 305), "uses")
    draw_dashed_arrow(draw, (1925, 460), (1925, 760), "uses")
    draw_dashed_arrow(draw, (1710, 400), (1570, 830), "uses")
    draw_dashed_arrow(draw, (470, 895), (610, 895), "uses")
    draw_dashed_arrow(draw, (1010, 895), (1150, 895), "uses")
    draw_dashed_arrow(draw, (1570, 895), (1710, 895), "uses")

    centered_text(
        draw,
        (80, 1250, 2520, 1325),
        "Рисунок 2.2 – Діаграма компонентів програмної системи",
        FONT_24,
    )

    img.save(DIAGRAMS / "05_component.png")
    img.save(DIAGRAMS / "figure_2_2_component.png")


def deployment():
    img = Image.new("RGB", (2700, 1550), BG)
    draw = ImageDraw.Draw(img)
    draw.text((850, 26), "UML Deployment Diagram: розгортання DinoJelly", font=FONT_30, fill=TEXT)

    draw_deployment_node(
        draw,
        (80, 300, 500, 520),
        "Client device",
        "device",
        ["Web browser", "Покупець / адміністратор", "HTTPS client"],
        BLUE,
    )

    cluster = (640, 140, 2600, 1360)
    draw.rounded_rectangle(cluster, radius=18, fill=(248, 250, 252), outline=LINE, width=4)
    draw.text((680, 165), "«node»", font=FONT_18, fill=MUTED)
    draw.text((680, 195), "Kubernetes cluster", font=FONT_26, fill=TEXT)

    draw_deployment_node(
        draw,
        (760, 300, 1120, 520),
        "NGINX Ingress",
        "executionEnvironment",
        ["TLS termination", "ssl-redirect", "host rules"],
        BLUE,
    )
    draw_artifact(
        draw,
        (1260, 330, 1640, 480),
        "laravel-service",
        ["Service type: LoadBalancer", "port 80 -> targetPort 80"],
        BG,
    )
    draw_deployment_node(
        draw,
        (1800, 250, 2440, 600),
        "laravel-app",
        "deployment",
        ["replicas: 3", "container: app", "image: ghcr.io/...:latest"],
        GREEN,
    )
    draw_artifact(
        draw,
        (1880, 445, 2360, 560),
        "Laravel runtime",
        ["Nginx + PHP-FPM", "Laravel 10 application"],
        BG,
    )

    draw_artifact(
        draw,
        (1260, 650, 1640, 810),
        "laravel-secrets",
        ["APP_KEY", "DB credentials", "database names"],
        BG,
    )
    draw_artifact(
        draw,
        (760, 900, 1120, 1040),
        "mysql-service",
        ["clusterIP: None", "port 3306"],
        BG,
    )
    draw_deployment_node(
        draw,
        (1260, 880, 1640, 1120),
        "mysql",
        "deployment",
        ["image: mysql:8.0", "replicas: 1", "containerPort: 3306"],
        GREEN,
    )
    draw_artifact(
        draw,
        (1260, 1220, 1640, 1325),
        "mysql-pvc",
        ["ReadWriteOnce, 10Gi", "/var/lib/mysql"],
        BG,
    )

    draw_artifact(
        draw,
        (1800, 900, 2140, 1040),
        "redis-service",
        ["port 6379", "targetPort 6379"],
        BG,
    )
    draw_deployment_node(
        draw,
        (2260, 880, 2520, 1120),
        "redis",
        "deployment",
        ["image: redis:7-alpine", "replicas: 1", "cache/session/queue"],
        GREEN,
    )

    draw_arrow(draw, (500, 410), (760, 410), "HTTPS")
    draw_arrow(draw, (1120, 410), (1260, 405), "HTTP")
    draw_arrow(draw, (1640, 405), (1800, 405), "targetPort 80")
    draw_arrow(draw, (2000, 600), (1640, 720), "env")
    draw_polyline_arrow(draw, [(1900, 600), (1900, 835), (1120, 835), (1120, 970)], "DB_HOST")
    draw_arrow(draw, (1120, 970), (1260, 1000), "TCP 3306")
    draw_arrow(draw, (1450, 1120), (1450, 1220), "volume mount")
    draw_polyline_arrow(draw, [(2120, 600), (2120, 835), (1800, 835), (1800, 970)], "REDIS_HOST")
    draw_arrow(draw, (2140, 970), (2260, 1000), "TCP 6379")

    centered_text(
        draw,
        (80, 1420, 2620, 1495),
        "Рисунок 2.5 – Діаграма розгортання інформаційної системи DinoJelly",
        FONT_24,
    )

    img.save(DIAGRAMS / "06_deployment.png")
    img.save(DIAGRAMS / "figure_2_5_deployment.png")


def split_catalog():
    src = SCREENSHOTS / "01_catalog.png"
    if not src.exists():
        return
    img = Image.open(src)
    width, height = img.size
    split_y = height // 2 + 120
    overlap = 140
    part1 = img.crop((0, 0, width, split_y))
    part2 = img.crop((0, max(0, split_y - overlap), width, height))
    part1.save(SCREENSHOTS / "01_catalog_part1.png")
    part2.save(SCREENSHOTS / "01_catalog_part2.png")


def split_admin_dashboard():
    src = SCREENSHOTS / "07_admin_dashboard.png"
    if not src.exists():
        return
    img = Image.open(src)
    width, height = img.size
    split_y = min(1180, height // 2 + 160)
    overlap = 120
    part1 = img.crop((0, 0, width, split_y))
    part2 = img.crop((0, max(0, split_y - overlap), width, height))
    part1.save(SCREENSHOTS / "07_admin_dashboard_part1.png")
    part2.save(SCREENSHOTS / "07_admin_dashboard_part2.png")


def main():
    DIAGRAMS.mkdir(parents=True, exist_ok=True)
    SCREENSHOTS.mkdir(parents=True, exist_ok=True)
    architecture()
    er_diagram()
    use_case()
    sequence()
    component()
    deployment()
    split_catalog()
    split_admin_dashboard()


if __name__ == "__main__":
    main()
