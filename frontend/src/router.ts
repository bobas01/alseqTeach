import { createRouter, createWebHistory } from 'vue-router'
import HomeView from './views/HomeView.vue'
import CheckoutView from './views/CheckoutView.vue'
import FaqView from './views/FaqView.vue'
import LessonView from './views/LessonView.vue'
import AdminView from './views/AdminView.vue'
import LegalView from './views/LegalView.vue'

export const router = createRouter({
  history: createWebHistory(),
  scrollBehavior(to) {
    if (to.hash) {
      return { el: to.hash, behavior: 'smooth' }
    }
    return { top: 0 }
  },
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/faq', name: 'faq', component: FaqView },
    { path: '/termos', name: 'termos', component: LegalView, props: { slug: 'termos' } },
    { path: '/privacidade', name: 'privacidade', component: LegalView, props: { slug: 'privacidade' } },
    { path: '/cancelamento', name: 'cancelamento', component: LegalView, props: { slug: 'cancelamento' } },
    { path: '/reserva/:id', name: 'checkout', component: CheckoutView, props: true },
    { path: '/aula/:id', name: 'lesson', component: LessonView, props: true },
    { path: '/admin', name: 'admin', component: AdminView },
  ],
})
