<template>
  <div id="app">
    <!-- CHMURA Z KOMUNIKATEM -->
    <Toast
      :text="respose"
      v-if="respose"
    />
    <!-- #CHMURA Z KOMUNIKATEM -->
    <!-- INFORMACJA O LADOWANIU DANYCH -->
    <div
      class="loading"
      v-if="!questions"
    >
      Ładowanie pytań...
    </div>
    <!-- #INFORMACJA O LADOWANIU DANYCH -->
    <!-- PYTANIA -->
    <div v-if="questions">
      <transition-group
        class="questions"
        name="list"
        tag="div"
      >
        <!-- PYTANIE -->
        <div
          class="question"
          v-for="(question, question_key) in questions"
          :key="question.id"
        >
          <!-- NAGŁÓWEK -->
          <div class="heading">
            <span>PYTANIE {{question_key + 1}}</span>
            <MainButton :onClick="() => removeQuestion(question_key)">
              <TrashIcon />
            </MainButton>
          </div>
          <!-- #NAGŁÓWEK -->
          <!-- OBRAZEK -->
          <div class="image-wrapper">
            <QuestionImage :media_id="question.image" />
            <div class="image-buttons">
              <MainButton :onClick="() => openModal(question_key)">
                <AddIcon v-if="!question.image" />
                <ChangeIcon v-if="question.image" />
              </MainButton>
              <MainButton :onClick="() => removeImage(question_key)">
                <TrashIcon />
              </MainButton>
            </div>
          </div>
          <!-- #OBRAZEK -->
          <!-- TREŚĆ PYTANIA -->
          <div class="title">
            <span>Treść pytania:</span>
          </div>
          <div class="input">
            <textarea
              type="text"
              placeholder="Wpisz tekst pytania..."
              v-model="question.text"
            />
            </div>
          <!-- #TREŚĆ PYTANIA -->
          <div class="title"> 
            <span>Odpowiedzi:</span>
          </div>
          <!-- ODPOWIEDZI -->
          <div class="answers" v-for="(answer, answer_key) in question.answers" :key="answer_key">
            <div class="number">{{answer_key + 1}}</div>
            <div class="input">
              <input type="text" placeholder="Wpisz tekst odpowiedzi..." v-model="question.answers[answer_key].text">
            </div>
            <!-- ODPOWIEDŹ -->
            <label class="checkbox">
              <input type="radio" :value="question.answers[answer_key].id" v-model="answers[question_key]" />
              <div class="value-box"></div>
            </label>
            <!-- #ODPOWIEDŹ -->
            <MainButton :onClick="() => removeAnswer(question_key, answer_key)"><TrashIcon /></MainButton>
          </div>
          <!-- #ODPOWIEDZI -->
          <!-- PRZYCISK NAWIGACYJNY -->
          <div class="buttons center">
            <MainButton :onClick="() => addAnswer(question_key)">Dodaj Odpowiedź</MainButton>
          </div>
          <!-- #PRZYCISK NAWIGACYJNY -->
        </div>
        <!-- #PYTANIE -->
      </transition-group>
      <!-- PRZYCISKI NAWIGACYJNE -->
      <div class="buttons">
        <MainButton :onClick="() => savePost()" color="green">Zapisz</MainButton>
        <MainButton :onClick="() => addQuestion()">Dodaj Pytanie</MainButton>
      </div>
      <!-- #PRZYCISKI NAWIGACYJNE -->
    </div>
    <!-- PYTANIA -->
  </div>
</template>

<script>
//przypisanie jQuery do zmiennej j
const j = jQuery.noConflict();
//import bibliotek
import QuestionImage from "./components/QuestionImage.vue";
import Toast from "./components/Toast.vue";
import MainButton from "./components/MainButton.vue";
import TrashIcon from "./components/icons/TrashIcon";
import AddIcon from "./components/icons/AddIcon";
import ChangeIcon from "./components/icons/ChangeIcon";

export default {
  name: "App",
  data() {
    return {
      questions: null,
      answers: null,
      respose: ""
    };
  },
  components: {
    QuestionImage,
    Toast,
    MainButton,
    TrashIcon,
    ChangeIcon,
    AddIcon
  },
   //pobieranie pytań po zainicjowaniu aplikacji
  mounted() {
    const self = this;
    j.ajax({
      type: "POST",
      dataType: "json",
      url: wp.ajax.settings.url,
      data: {
        action: "get_fields",
        post_id: self.postID
      },
      success: function(response) {
        if (JSON.parse(response.questions) && JSON.parse(response.answers)) {
          self.questions = JSON.parse(response.questions);
          self.answers = JSON.parse(response.answers);
        } else {
          self.questions = [
            {
              id: 0,
              text: "",
              image: null,
              answers: [{ id: 0, text: "" }]
            }
          ];
          self.answers = ["0"];
        }
      },
      error: function(error) {
        self.respose = "Problem z pobraniem quizu";
        console.log(error);
        setTimeout(() => {
          self.respose = "";
        }, 3000);
      }
    });
  },
  methods: {
    //zapisywanie quizu
    savePost() {
      const self = this;
      let postTitle = "Quiz " + self.postID;
      if (j("input[name*='post_title']").val()) {
        postTitle = j("input[name*='post_title']").val();
      }
      j.ajax({
        type: "POST",
        url: wp.ajax.settings.url,
        dataType: "json",
        data: {
          action: "update_fields",
          post_id: self.postID,
          post_title: postTitle,
          questions: self.questions,
          answers: self.answers
        },
        success: function(response) {
          if (response) {
            window.location.href = response;
          } else {
            self.respose = "Quiz został zapisany";
            setTimeout(() => {
              self.respose = "";
            }, 3000);
          }
        },
        error: function(error) {
          self.respose = "Problem z zapisem quizu";
          console.log(error);
          setTimeout(() => {
            self.respose = "";
          }, 3000);
        }
      });
    },
    //modal dodawania obrazka
    openModal(questionID) {
      const self = this;
      var file_frame;
      file_frame = wp.media.frames.file_frame = wp.media({
        title: "Wybierz obraz",
        button: {
          text: "Użyj"
        },
        multiple: false
      });
      file_frame.on("select", function() {
        wp.media.attachment = file_frame
          .state()
          .get("selection")
          .first()
          .toJSON();
        self.$set(
          self.questions[questionID],
          "image",
          String(wp.media.attachment.id)
        );
      });
      file_frame.open();
    },
    //usuwanie obrazka
    removeImage(questionID) {
      this.$set(this.questions[questionID], "image", null);
    },
    //dodawanie pytania
    addQuestion() {
      this.answers = [...this.answers, "0"];
      this.questions = [
        ...this.questions,
        {
          id: this.findNextIndex(this.questions),
          text: "",
          image: null,
          answers: [{ id: 0, text: "" }]
        }
      ];
      this.respose = "Pytanie dodane";
      setTimeout(() => {
        this.respose = "";
      }, 3000);
    },
    //usuwanie pytania
    removeQuestion(questionID) {
      this.answers.splice(questionID, 1);
      this.questions.splice(questionID, 1);
      if (this.answers.length <= 0 && this.questions.length <= 0) {
        this.questions = [
          {
            id: 1,
            text: "",
            image: null,
            answers: [{ id: 0, text: "" }]
          }
        ];
        this.answers = ["0"];
      }
      this.respose = "Pytanie " + (questionID + 1) + " usunięte";
      setTimeout(() => {
        this.respose = "";
      }, 3000);
    },
    //dodawanie odpowiedzi
    addAnswer(questionID) {
      this.$set(this.questions[questionID], "answers", [
        ...this.questions[questionID].answers,
        { id: this.findNextIndex(this.questions[questionID].answers), text: "" }
      ]);
    },
    //usuwanie odpowiedzi
    removeAnswer(questionID, answerID) {
      this.answers[questionID] = "0";
      this.questions[questionID].answers.splice(answerID, 1);
      if (this.questions[questionID].answers <= 0) {
        this.questions[questionID].answers = [{ id: 0, text: "" }];
      }
    },
    //szukanie kolejnego indexu w tablicy
    findNextIndex(items) {
      return Math.max.apply(Math, items.map(i => parseInt(i.id))) + 1;
    }
  }
};
</script>

<style lang="scss">
@import "../scss/variables.scss";
#postbox-container-1 {
  display: none;
}
#vue-quiz {
  max-width: 680px;
  margin: 0 auto 40px auto;
  font-size: 18px;
  line-height: 24px;
  color: $color3;
  .loading {
    text-align: center;
    font-size: 20px;
    line-height: 30px;
    margin-top: 50px;
  }
  .buttons {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 20px;
    &.center {
      justify-content: center;
    }
  }
  .questions {
    .image-wrapper {
      position: relative;
      margin: 0 auto;
      border-radius: 10px;
      overflow: hidden;
      .image-buttons {
        position: absolute;
        right: 20px;
        bottom: 20px;
        display: flex;
        align-items: center;
        .main-button {
          margin: 0 5px;
        }
      }
    }
    .title {
      display: flex;
      justify-content: space-between;
      margin: 20px 0px 10px 0px;
    }
    .question {
      padding: 20px;
      margin-top: 40px;
      border: 1px solid $color4;
      border-radius: 6px;
      box-shadow: 2px 2px 1px 0px rgba(0, 0, 0, 0.2);
      .heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 24px;
        line-height: 30px;
        font-weight: bold;
        margin-bottom: 20px;
      }
      .answers {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        .number {
          width: 30px;
          padding: 5px;
          text-align: center;
        }
      }
      .input {
        width: 100%;
        input {
          width: 100%;
          padding: 8px 10px;
          border-radius: 10px;
          border: 1px solid $color4;
        }
        textarea {
          width: 100%;
          min-height: 80px;
          padding: 8px 10px;
          border-radius: 10px;
          border: 1px solid $color4;
        }
      }
      .checkbox {
        margin-left: 10px;
        margin-right: 10px;
        input {
          display: none;
        }
        input:checked {
          & + .value-box {
            background: $green;
            &:before {
              content: "\2713";
            }
          }
        }
        .value-box {
          display: flex;
          align-items: center;
          justify-content: center;
          position: relative;
          width: 34px;
          height: 34px;
          border-radius: 10px;
          background: $red;
          color: $white;
        }
      }
    }
  }
  .save {
    margin: 40px 0 20px 0;
    button {
      width: 100%;
    }
  }
}
.list-enter-active,
.list-leave-active {
  transition: all 1s;
}
.list-enter,
.list-leave-to {
  opacity: 0;
  transform: translateX(-100px);
}
</style>