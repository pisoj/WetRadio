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
        prepend-icon="mdi-radio-tower"
        title="Show settings"
      >
        <v-card-text>
          <v-text-field
            label="Title"
            variant="underlined"
            v-model="show.title"
            :rules="notEmptyRule"
          ></v-text-field>
          <v-text-field
            label="Subtitle (optional)"
            variant="underlined"
            v-model="show.subtitle"
          ></v-text-field>
          <v-radio-group v-model="show.category_id">
            <v-row
              v-for="i in categoryNumber"
              :key="i"
              class="ms-1 mt-n1 d-flex align-center"
            >
              <v-col cols="1" sm="1">
                <v-radio :value="categories[i - 1].id"></v-radio>
              </v-col>
              <v-col cols="12" sm="5">
                <v-text-field
                  label="Title"
                  variant="underlined"
                  v-model="categories[i - 1].title"
                  :rules="notEmptyRule"
                ></v-text-field>
              </v-col>
              <v-col cols="5" sm="2">
                <v-text-field
                  label="Priority"
                  variant="underlined"
                  type="number"
                  v-model="categories[i - 1].priority"
                  :rules="integerRule"
                ></v-text-field>
              </v-col>
              <v-col cols="6" sm="4" class="d-flex">
                <v-dialog
                  v-if="categories[i - 1].id !== undefined"
                  v-model="categoryDeleteDialogState[i - 1]"
                  width="500"
                >
                  <template v-slot:activator="{ props }">
                    <v-btn
                      icon="mdi-delete"
                      color="red"
                      variant="text"
                      v-bind="props"
                    ></v-btn>
                  </template>
                  <v-card>
                    <v-card-title class="mt-2 text-h5"
                      >Delete {{ categories[i - 1].title }}?</v-card-title
                    >
                    <v-card-text
                      >Are you sure you want to delete show category
                      {{ categories[i - 1].title }}? This will also delete all
                      shows inside it and all recordings of those shows. This
                      action is permanent and cannot be undone.</v-card-text
                    >
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn
                        variant="text"
                        @click="categoryDeleteDialogState[i - 1] = false"
                      >
                        Cancel
                      </v-btn>
                      <v-btn
                        color="red"
                        variant="text"
                        @click="
                          categoryNumber -= 1;
                          categories.splice(i - 1, 1);
                          originalCategories.splice(i - 1, 1);
                          categoryDeleteDialogState[i - 1] = false;
                        "
                      >
                        Delete
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
                <v-btn
                  v-else
                  icon="mdi-minus"
                  variant="text"
                  @click="
                    categoryNumber -= 1;
                    categories.splice(i - 1, 1);
                    originalCategories.splice(i - 1, 1);
                    categoryDeleteDialogState[i - 1] = false;
                  "
                ></v-btn>
                <v-btn
                  v-if="i === categoryNumber"
                  icon="mdi-plus"
                  variant="text"
                  @click="
                    categoryNumber += 1;
                    categories.push({ id: undefined, title: '', priority: 0 });
                    originalCategories.push({
                      id: -1,
                      title: '',
                      priority: 0,
                    });
                  "
                ></v-btn>
                <v-btn
                  v-if="
                    categories[i - 1].title !=
                      originalCategories[i - 1].title ||
                    categories[i - 1].priority !=
                      originalCategories[i - 1].priority
                  "
                  icon="mdi-check"
                ></v-btn>
              </v-col>
            </v-row>
          </v-radio-group>
          <v-text-field
            label="Priority"
            type="number"
            hint="Where the show will be placed relative to other shows in the same category. i.e. abowe or below."
            persistent-hint
            variant="underlined"
            v-model="show.priority"
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
                >Delete {{ show.title }}?</v-card-title
              >
              <v-card-text
                >Are you sure you want to delete show {{ show.title }}? This
                action is permanent and cannot be undone.</v-card-text
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
import { defineComponent } from "vue";
import { AxiosError } from "axios";
import { readonly } from "vue";

export default defineComponent({
  name: "ShowView",

  data(): {
    error: {
      title: string;
      message: string;
      user: boolean;
    } | null;
    show: {
      id: number | undefined;
      title: string;
      subtitle: string;
      image: string;
      is_replayable: boolean;
      category_id: number;
      priority: number;
    };
    categories: {
      id: number | undefined;
      title: string;
      priority: number;
    }[];
    originalCategories: {
      readonly id: number;
      readonly title: string;
      readonly priority: number;
    }[];
    categoryDeleteDialogState: boolean[];
    categoryNumber: number;
    notEmptyRule: any;
    integerRule: any;
    isNew: boolean;
    deleteDialogState: boolean;
  } {
    return {
      error: null,
      show: {
        id: undefined,
        title: "",
        subtitle: "",
        image: "",
        is_replayable: true,
        category_id: 0,
        priority: 0,
      },
      categories: [],
      originalCategories: [],
      categoryDeleteDialogState: [],
      categoryNumber: 0,
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
      isNew: false,
      deleteDialogState: false,
    };
  },

  methods: {
    priorityToNumber() {
      this.show.priority = parseInt(this.show.priority.toString());
    },

    delet() {
      this.axios
        .delete("http://10.1.10.20/admin/api/show.php", {
          params: {
            id: this.show.id,
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
                  "Station " + this.show.title + " deleted successfully.",
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
          url: "http://10.1.10.20/admin/api/show.php",
          method: this.isNew ? "post" : "put",
          data: this.show,
        })
        .then((_) => {
          const alert = this.isNew
            ? {
                title: "Created successfully",
                message:
                  "Station " + this.show.title + " created successfully.",
              }
            : {
                title: "Saved successfully",
                message:
                  "Seccessfully saved settings of station " +
                  this.show.title +
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
    this.axios
      .get("http://10.1.10.20/admin/api/show-categories.php")
      .then((response) => {
        this.categories = response.data;
        this.originalCategories = JSON.parse(JSON.stringify(response.data)); // Clone object
        this.categoryNumber = this.categories.length || 1;
      })
      .catch(
        (error: AxiosError) =>
          (this.error = {
            title: <string>(<unknown>error.response?.status),
            message: <string>error.response?.data,
            user: false,
          })
      );

    this.show.id = parseInt(<string>this.$route.params.id);
    if (Number.isNaN(this.show.id)) {
      this.isNew = true;
      this.show.id = undefined;
      return;
    }
    this.axios
      .get("http://10.1.10.20/admin/api/show.php", {
        params: {
          id: this.show.id,
        },
      })
      .then((response) => (this.show = { ...this.show, ...response.data }))
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
