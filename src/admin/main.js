import Vue from "vue";
import App from "./App";

Vue.config.productionTip = false;

Vue.mixin({
  data: function() {
    return {
      get postID() {
        return document.getElementById('vue-quiz').getAttribute("data-id");
      }
    }
  }
});

new Vue({
  el: "#app",
  components: { App },
  template: "<App/>"
});
