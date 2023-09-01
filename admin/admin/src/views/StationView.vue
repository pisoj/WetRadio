<template>
  <v-container class="d-flex justify-center">
    <div>
      <v-alert
        v-if="error"
        type="error"
        class="mb-4"
        variant="elevated"
        :title="error.title"
        :text="error.message"
      ></v-alert>
      <v-card
        v-if="error?.user || !error"
        class="only-card"
        prepend-icon="mdi-radio"
        title="Station settings"
      >
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
          <v-dialog v-model="deleteDialogState" width="500">
            <template v-slot:activator="{ props }">
              <v-btn
                v-if="!isNew"
                prepend-icon="mdi-delete"
                color="red"
                v-bind="props"
                >Delete</v-btn
              >
            </template>
            <v-card>
              <v-card-title class="mt-2 text-h5"
                >Delete {{ station.title }}?</v-card-title
              >
              <v-card-text
                >Are you sure you want to delete station {{ station.title }}?
                This action is permanent and cannot be undone.</v-card-text
              >
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn variant="text" @click="deleteDialogState = false">
                  Cancel
                </v-btn>
                <v-btn
                  color="red"
                  variant="text"
                  @click="
                    deleteDialogState = false;
                    delet();
                  "
                >
                  Delete
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-spacer></v-spacer>
          <v-btn
            @click="
              $router.push({
                name: 'dashboard',
                replace: true,
              })
            "
            >Cancel</v-btn
          >
          <v-btn
            append-icon="mdi-content-save"
            variant="elevated"
            @click="save()"
            >Save</v-btn
          >
        </v-card-actions>
      </v-card>
    </div>
  </v-container>
</template>

<script lang="ts">
import { AxiosError } from "axios";
import { defineComponent } from "vue";

export default defineComponent({
  name: "StationView",

  data(): {
    error: {
      title: string;
      message: string;
      user: boolean;
    } | null;
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
    isNew: boolean;
    deleteDialogState: boolean;
  } {
    return {
      error: null,
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
          try {
            new URL(value!);
          } catch (_) {
            return "Not a valid URL.";
          }
          return true;
        },
      ],
      isNew: false,
      deleteDialogState: false,
    };
  },

  methods: {
    priorityToNumber() {
      this.station.priority = parseInt(this.station.priority.toString());
    },

    delet() {
      this.axios
        .delete("http://10.1.10.20/admin/api/station.php", {
          params: {
            id: this.station.id,
          },
        })
        .then((_) => {
          this.$router.push({
            name: "dashboard",
            replace: true,
            state: {
              successAlert: {
                title: "Deleted successfully",
                message:
                  "Station " + this.station.title + " deleted successfully.",
              },
            },
          });
        })
        .catch(
          (error: AxiosError) =>
            (this.error = {
              title: <string>(<unknown>error.response?.status),
              message: <string>error.response?.data,
              user: true,
            })
        );
    },
    save() {
      this.axios
        .request({
          url: "http://10.1.10.20/admin/api/station.php",
          method: this.isNew ? "post" : "put",
          data: {
            ...this.station,
            ...{
              endpoints: JSON.stringify(this.station.endpoints),
              endpoint_names: JSON.stringify(this.station.endpoint_names),
            },
          },
        })
        .then((_) => {
          const alert = this.isNew
            ? {
                title: "Created successfully",
                message:
                  "Station " + this.station.title + " created successfully.",
              }
            : {
                title: "Saved successfully",
                message:
                  "Seccessfully saved settings of station " +
                  this.station.title +
                  ".",
              };
          this.$router.push({
            name: "dashboard",
            replace: true,
            state: {
              successAlert: alert,
            },
          });
        })
        .catch(
          (error: AxiosError) =>
            (this.error = {
              title: <string>(<unknown>error.response?.status),
              message: <string>error.response?.data,
              user: true,
            })
        );
    },
  },

  created() {
    this.station.id = parseInt(<string>this.$route.params.id);
    if (Number.isNaN(this.station.id)) {
      this.isNew = true;
      this.station.id = undefined;
      return;
    }
    this.axios
      .get("http://10.1.10.20/admin/api/station.php", {
        params: {
          id: this.station.id,
        },
      })
      .then((response) => {
        response.data.endpoints = JSON.parse(response.data.endpoints);
        response.data.endpoint_names = JSON.parse(response.data.endpoint_names);
        this.station = { ...this.station, ...response.data };
        this.endpointNumber = this.station.endpoints.length || 1;
      })
      .catch(
        (error: AxiosError) =>
          (this.error = {
            title: <string>(<unknown>error.response?.status),
            message: <string>error.response?.data,
            user: false,
          })
      );
  },
});
</script>
