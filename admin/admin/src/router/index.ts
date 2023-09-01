import { createRouter, createWebHashHistory, RouteRecordRaw } from "vue-router";
import DashboardView from "../views/DashboardView.vue";

const routes: Array<RouteRecordRaw> = [
  {
    path: "/",
    name: "dashboard",
    component: DashboardView,
  },
  {
    path: "/station/:id",
    name: "station",
    component: () =>
      import(/* webpackChunkName: "station" */ "../views/StationView.vue"),
  },
  {
    path: "/show/:id",
    name: "show",
    component: () =>
      import(/* webpackChunkName: "show" */ "../views/ShowView.vue"),
  },
];

const router = createRouter({
  history: createWebHashHistory(process.env.BASE_URL),
  routes,
});

export default router;
