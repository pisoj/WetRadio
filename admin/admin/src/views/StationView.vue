<template>
  <v-container class="d-flex justify-center">
    <v-card class="only-card" title="Station settings">
      <v-card-text>
        <v-text-field
          label="Title"
          variant="underlined"
          v-model="station.title"
          :rules="notEmptyRule"
        ></v-text-field>
        <v-text-field
          label="Description (optional)"
          variant="underlined"
          v-model="station.description"
        ></v-text-field>
        <v-list-item-subtitle class="mt-3">Endpoints:</v-list-item-subtitle>
        <v-row
          v-for="i in endpointNumber"
          :key="i"
          class="ms-1 mt-n1 d-flex align-center"
        >
          <v-col cols="12" sm="5" md="6">
            <v-text-field
              label="URL"
              placeholder="https://example.com/mystream"
              variant="underlined"
              v-model="station.endpoints[i - 1]"
              :rules="urlRule"
            ></v-text-field>
          </v-col>
          <v-col cols="7" sm="4" md="4">
            <v-text-field
              label="Title"
              hint="Will be displayed to the user when selecting endpoint."
              persistent-hint
              variant="underlined"
              v-model="station.endpoint_names[i - 1]"
              :rules="notEmptyRule"
            ></v-text-field>
          </v-col>
          <v-col cols="4" sm="2" md="1">
            <v-btn
              v-if="i === endpointNumber"
              icon="mdi-plus"
              @click="endpointNumber += 1"
            ></v-btn>
            <v-btn
              v-else
              icon="mdi-minus"
              @click="
                endpointNumber -= 1;
                station.endpoints.splice(i - 1, 1);
                station.endpoint_names.splice(i - 1, 1);
              "
            ></v-btn>
          </v-col>
        </v-row>
        <v-radio-group
          class="mt-4"
          label="Endpoint selection strategy:"
          v-model="station.endpoint_order"
        >
          <v-radio label="Ordered" value="ordered"></v-radio>
          <v-radio label="Random" value="random"></v-radio>
          <v-list-item-subtitle
            >Ordered trys endpoints in the specified order until it finds a
            working one (for endpoints of different quality). Random will try
            them in a randomized order (for load balancing). When random is
            selected users are not able to choose
            endpoint.</v-list-item-subtitle
          >
        </v-radio-group>
        <v-text-field
          label="Priority"
          type="number"
          hint="Where the station will show relative to other stations. i.e. abowe or below."
          persistent-hint
          variant="underlined"
          v-model="station.priority"
          @change="priorityToNumber()"
          :rules="integerRule"
        ></v-text-field>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn append-icon="mdi-content-save" variant="elevated">Save</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "StationView",

  data(): {
    station: {
      id: number | undefined;
      title: string;
      description: string;
      endpoints: string[];
      endpoint_names: string[];
      endpoint_order: string;
      priority: number;
    };
    endpointNumber: number;
    notEmptyRule: any;
    integerRule: any;
    urlRule: any;
  } {
    return {
      station: {
        id: undefined,
        title: "",
        description: "",
        endpoints: [],
        endpoint_names: [],
        endpoint_order: "ordered",
        priority: 0,
      },
      endpointNumber: 1,
      notEmptyRule: [
        (value: string | null) => {
          if (value?.length || 0 > 0) return true;
          return "This field is required.";
        },
      ],
      integerRule: [
        (value: any) => {
          if (!isNaN(parseInt(value))) return true;
          return "The value mus be an integer.";
        },
      ],
      urlRule: [
        (value: string | null) => {
          if (
            !value?.match(
              /(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g
            )
          ) {
            return "Not a valid URL.";
          }
          return true;
        },
      ],
    };
  },

  methods: {
    priorityToNumber() {
      this.station.priority = parseInt(this.station.priority.toString());
    },
  },

  mounted() {
    this.endpointNumber = this.station.endpoints.length || 1;
  },
});
</script>

<style>
.only-card {
  width: 100%;
  max-width: 75rem;
}
</style>
