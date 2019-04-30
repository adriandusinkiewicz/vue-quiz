import Vue from "vue";
import App from "./App";

Vue.config.productionTip = false;

//łapanie wszystkich quizów po klasie
var quizes = document.getElementsByClassName("vue-quiz");

//tworzenie instancji quizu
Array.prototype.forEach.call(quizes, function (quiz) {
  new Vue({
    el: "#" + quiz.id,
    data: function () {
      return {
        postID: quiz.getAttribute("data-id")
      }
    },
    components: {
      App
    },
    template: "<App/>"
  });
});