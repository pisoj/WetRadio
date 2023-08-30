<template>
  <v-layout>
    <v-app-bar
      density="compact"
      color="black"
      scroll-behavior="fade-image"
      image="/banner.jpg"
      title="WetRadio"
    >
      <template v-slot:append>
        <v-btn icon="mdi-cog"></v-btn>
        <v-menu transition="scale-transition">
          <template v-slot:activator="{ props }">
            <v-btn icon="mdi-dots-vertical" v-bind="props"></v-btn>
          </template>

          <v-list>
            <v-list-item prepend-icon="mdi-account-circle" value>
              <v-list-item-title>My account</v-list-item-title>
            </v-list-item>
            <v-list-item prepend-icon="mdi-invert-colors" value>
              <v-list-item-title>Change theme</v-list-item-title>
            </v-list-item>
            <v-list-item prepend-icon="mdi-logout" value>
              <v-list-item-title>Logout</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-app-bar>
    <v-main>
      <router-view />
      <div style="height: 100vh"></div>
    </v-main>
  </v-layout>
</template>
