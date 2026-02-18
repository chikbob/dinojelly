export default {
    header: {
        address: "Prospekt Mira, 111",
        return: "Returns",
        payment: "Payment",
        gift: "Gift Certificate",
        home: "Home",
        login: "Log in",
        logout: "Log out",
        favorites: "Favorites",
        cart: "Cart",
        orders: "Orders",
    },
    footer: {
        help: "Help",
        return: "Returns",
        payment: "Payment",
        copyright: "\"DINOJELLY\". All rights reserved."
    },
    catalog: {
        title: "Jelly Catalog",
        addToCart: "Add to cart"
    },
    cart: {
        title: "Your Cart",
        empty: "Your cart is empty.",
        delete: "Remove",
        total: "Total amount",
        clear: "Clear cart",
        checkout: "Checkout",
        deliveryHint: "Available delivery options and times can be selected during checkout",
        yourCart: "Your cart",
        items: "Items",
        itemsShort: "pcs",
        discount: "Discount",
        finalTotal: "Final total",
    },
    product: {
        addToCart: "Add to cart"
    },
    profile: {
        title: "User Profile",
        name: "Name",
        email: "Email",
        verified: "Email verified",
        notVerified: "Email not verified",
        phone: "Phone",
        address: "Address",
        registeredAt: "Registration date",
        edit: "Edit",
        notProvided: "Not provided",
        save: "Save",
        cancel: "Cancel",
    },
    auth: {
        loginTitle: "Log in",
        registerTitle: "Register",
        email: "Email",
        password: "Password",
        passwordConfirm: "Confirm password",
        name: "Name",
        phone: "Phone",
        noAccount: "Don't have an account?",
        haveAccount: "Already have an account?",
        wait: "Please wait...",
        login: "Log in",
        register: "Sign up",
        failed: "Invalid email or password",
        register_success: "Registration successful! Welcome.",
        logged_out: "You have successfully logged out.",
    },
    validation: {
        name_required: "Name is required",
        phone_required: "Phone number is required",
        email_required: "Email is required",
        email_email: "Invalid email format",
        email_unique: "This email is already registered",
        password_required: "Password is required",
        password_min: "Password must contain at least 8 characters",
        password_confirmed: "Passwords do not match",
    },
    favorites: {
        title: "Your Favorites",
        empty: "You have no favorite items yet.",
        sort: "Sort",
        newFirst: "Newest first",
        oldFirst: "Oldest first",
        cheapFirst: "Cheapest first",
        expensiveFirst: "Most expensive first",
    },
    currency: {
        symbol: "$"
    },
    orders: {
        title: "My Orders",
        filterByStatus: "Filter by status",
        all: "All",
        pending: "Processing",
        completed: "Completed",
        canceled: "Canceled",

        status: {
            pending: "Processing",
            completed: "Completed",
            canceled: "Canceled"
        },

        amount: "Amount",
        itemsCount: "Items",
        payment: "Payment",
        date: "Date",
        card: "Card",
        cash: "Cash",

        empty: "You have no orders yet"
    },
    order: {
        orderNumber: "Order",
        payment: "Payment",
        amount: "Amount",
        quantity: "Number of items",
        items: "Items in order",
        statusText: "Order status",

        status: {
            pending: "Processing",
            completed: "Completed",
            canceled: "Canceled"
        },

        card: "Card",
        cash: "Cash",

        cancel: "Cancel order",
        canceling: "Canceling...",
        confirmCancel: "Are you sure you want to cancel this order?",
        back: "Back to orders"
    },

    checkout: {
        title: "Checkout",
        items: "Items",
        total: "Total",
        choosePayment: "Choose a payment method",
        payCard: "Pay by card",
        payCash: "Pay with cash"
    },

    pagination: {
        previous: "Previous",
        next: "Next",
    },

    admin: {
        sidebar: {
            dashboard: "Дашборд",
            products: "Товары",
            orders: "Заказы",
            users: "Пользователи",
        },
        orders: {
            title: "Orders",
            id: "ID",
            status: "Status",
            totalPrice: "Total Price",
            createdAt: "Created At",
            actions: "Actions",
            view: "View",
            orderNumber: "Order",

            statuses: {
                pending: "Pending",
                completed: "Completed",
                canceled: "Canceled",
            }
        },
        dashboard: {
            title: "Dashboard",
            users: "Users",
            products: "Products",
            orders: "Orders",
            chartTitle: "Orders by days",
        },
        users: {
            title: "Users",
            id: "ID",
            name: "Name",
            email: "Email",
        },
        products: {
            title: "Products",
            createNew: "Create New Product",
            id: "ID",
            image: "Image",
            name: "Name",
            weight: "Weight",
            price: "Price",
            oldPrice: "Old Price",
            actions: "Actions",
            edit: "Edit",
            delete: "Delete",
            confirmDelete: "Are you sure you want to delete this product?",

            editProduct: "Edit Product",
            createProduct: "Create Product",
            save: "Save",
            create: "Create",
            optional: "optional",

            fields: {
                name: "Name",
                weight: "Weight",
                price: "Price",
                oldPrice: "Old Price",
                description: "Description",
                imageUrl: "Image URL",
                image: "Image",
            }
        },
        header: {
            title: "Админ-панель",
            logout: "Выйти",
        }
    }


}
