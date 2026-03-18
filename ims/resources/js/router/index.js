/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */

import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth.js'

const routes = [
    {
        path: '/ims/login',
        name: 'login',
        component: () => import('@/views/Login.vue'),
        meta: { public: true }
    },
    {
        path: '/ims',
        component: () => import('@/components/Layout/AppLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            { path: '', redirect: '/ims/dashboard' },
            { path: 'dashboard', name: 'dashboard', component: () => import('@/views/Dashboard.vue') },
            { path: 'products', name: 'products', component: () => import('@/views/Products/ProductList.vue') },
            { path: 'products/:id', name: 'product-detail', component: () => import('@/views/Products/ProductDetail.vue') },
            { path: 'sources', name: 'sources', component: () => import('@/views/Sources/SourceList.vue'), meta: { requiresAdmin: true } },
            { path: 'sources/:code/locations', name: 'source-locations', component: () => import('@/views/Sources/SourceLocations.vue'), meta: { requiresAdmin: true } },
            { path: 'movements', name: 'movements', component: () => import('@/views/Movements/MovementList.vue') },
            { path: 'purchase-orders', name: 'purchase-orders', component: () => import('@/views/PurchaseOrders/POList.vue') },
            { path: 'purchase-orders/:id', name: 'po-detail', component: () => import('@/views/PurchaseOrders/PODetail.vue') },
            { path: 'purchase-orders/new', name: 'po-new', component: () => import('@/views/PurchaseOrders/PODetail.vue') },
            { path: 'suppliers', name: 'suppliers', component: () => import('@/views/Suppliers/SupplierList.vue'), meta: { requiresAdmin: true } },
            { path: 'orders', name: 'orders', component: () => import('@/views/Orders/OrderList.vue') },
            { path: 'orders/:id/fulfill', name: 'order-fulfill', component: () => import('@/views/Orders/OrderFulfill.vue') },
            { path: 'fulfillments', name: 'fulfillments', component: () => import('@/views/Fulfillments/FulfillmentList.vue') },
            { path: 'alerts', name: 'alerts', component: () => import('@/views/Alerts/AlertList.vue') },
            { path: 'reports/low-stock', name: 'report-low-stock', component: () => import('@/views/Reports/LowStockReport.vue') },
            { path: 'reports/stock-value', name: 'report-stock-value', component: () => import('@/views/Reports/StockValueReport.vue') },
            { path: 'reports/movements', name: 'report-movements', component: () => import('@/views/Reports/MovementReport.vue') },
        ]
    },
    { path: '/:pathMatch(.*)*', redirect: '/ims' }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach((to, from, next) => {
    const auth = useAuthStore()

    if (!to.meta.public && !auth.isAuthenticated) {
        return next('/ims/login')
    }

    if (to.meta.requiresAdmin && !auth.isAdmin) {
        return next('/ims/dashboard')
    }

    if (to.path === '/ims/login' && auth.isAuthenticated) {
        return next('/ims/dashboard')
    }

    next()
})

export default router
