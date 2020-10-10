require("./bootstrap");
import Vue from "vue";
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import Task from "@/components/Task";

Vue.config.productionTip = false;

Vue.use(BootstrapVue)
Vue.use(IconsPlugin)

Vue.mixin({
  methods: {
    API: function (url){
      return this.$root.$data.apiUrl + url;
    },
  }
})

new Vue({
  data: {apiUrl: null, tasks: [], onSuccess: null, onFail: null},
  beforeMount: function () {
      this.apiUrl = this.$el.attributes['data-api-url'].value;
      this.tasks = JSON.parse(this.$el.attributes['tasks'].value);
      if(this.$el.attributes['onSuccess']){
        this.onSuccess = this.$el.attributes['onSuccess'].value;
      }
      if(this.$el.attributes['onFail']){
        this.onFail = this.$el.attributes['onFail'].value;
      }
  },
  render: function (h) { 
      return h(Task, {
        props: {
          'tasks': this.tasks,
          'onSuccess': this.onSuccess,
          'onFail': this.onFail,
        }
      })
  }
}).$mount("#task");