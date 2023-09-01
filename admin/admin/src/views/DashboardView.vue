<template>
  <v-container>
    <v-alert
      v-if="successAlert"
      type="success"
      class="mb-4"
      variant="elevated"
      :title="successAlert.title"
      :text="successAlert.message"
    ></v-alert>
    <v-row>
      <v-col cols="12" md="6">
        <v-card prepend-icon="mdi-radio">
          <template v-slot:title>
            <div class="d-flex align-center justify-space-between">
              Stations
              <v-btn
                icon="mdi-plus"
                variant="text"
                @click="
                  $router.push({ name: 'station', params: { id: 'new' } })
                "
              ></v-btn>
            </div>
          </template>
          <v-list lines="two">
            <v-list-item
              v-for="station in stations"
              :key="station"
              :value="station.id"
              @click="
                $router.push({ name: 'station', params: { id: station.id } })
              "
            >
              <div class="d-flex align-center justify-space-between">
                <div>
                  <v-list-item-title>{{ station.title }}</v-list-item-title>
                  <v-list-item-subtitle>{{
                    station.description
                  }}</v-list-item-subtitle>
                </div>
                <v-chip color="green">On Air</v-chip>
              </div>
            </v-list-item>
          </v-list>
        </v-card>

        <v-card class="mt-4" prepend-icon="mdi-message-text">
          <template v-slot:title>
            <div class="d-flex align-center justify-space-between">
              Sends
              <v-btn icon="mdi-plus" variant="text"></v-btn>
            </div>
          </template>
          <v-list lines="one">
            <v-list-item
              v-for="sendType in sendTypes"
              :key="sendType"
              :value="sendType.id"
            >
              <v-list-item-title class="d-flex justify-space-between">
                {{ sendType.title }}
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card prepend-icon="mdi-radio-tower">
          <template v-slot:title>
            <div class="d-flex align-center justify-space-between">
              Shows
              <v-btn icon="mdi-plus" variant="text"></v-btn>
            </div>
          </template>
          <v-list lines="two">
            <div v-for="category in shows" :key="category">
              <b class="pl-4">{{ category.category_title }}</b>
              <v-list-item
                class="pl-8"
                v-for="show in category.shows"
                :key="show"
                :value="show.id"
                @click="$router.push({ name: 'show', params: { id: show.id } })"
              >
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <v-list-item-title>{{ show.title }}</v-list-item-title>
                    <v-list-item-subtitle>{{
                      show.subtitle
                    }}</v-list-item-subtitle>
                  </div>
                  <v-btn
                    v-if="show.is_replayable"
                    icon="mdi-music-box-multiple"
                    variant="text"
                  ></v-btn>
                </div>
              </v-list-item>
            </div>
          </v-list>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "DashboardView",

  data(): {
    stations: any;
    shows: any;
    sendTypes: any;
    successAlert:
      | {
          title: string;
          message: string;
        }
      | undefined;
  } {
    return {
      stations: null,
      shows: null,
      sendTypes: null,
      successAlert: history.state.successAlert,
    };
  },

  created() {
    this.axios
      .get("http://10.1.10.20/admin/api/stations.php")
      .then((response) => (this.stations = response.data));
    this.axios
      .get("http://10.1.10.20/admin/api/shows.php")
      .then((response) => (this.shows = response.data));
    this.axios
      .get("http://10.1.10.20/admin/api/send-types.php")
      .then((response) => (this.sendTypes = response.data));
  },
});
</script>
