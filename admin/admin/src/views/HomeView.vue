<template>
  <v-container>
    <v-row>
      <v-col cols="12" md="6">
        <v-card title="Stations">
          <v-list lines="one">
            <v-list-item value="zfm">
              <v-list-item-title class="d-flex justify-space-between">
                ZFM
                <v-chip color="green">On Air</v-chip>
              </v-list-item-title>
            </v-list-item>
            <v-list-item value="top">
              <v-list-item-title class="d-flex justify-space-between">
                Top radio
                <v-chip>Off Air</v-chip>
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-card>
        <v-card title="Stations"></v-card>
        <v-card title="Stations"></v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card title="Stations"></v-card>
        <v-card title="Stations"></v-card>
        <v-card title="Stations"></v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import HelloWorld from "@/components/HelloWorld.vue"; // @ is an alias to /src

export default defineComponent({
  name: "HomeView",
  components: {
    HelloWorld,
  },
});
</script>
