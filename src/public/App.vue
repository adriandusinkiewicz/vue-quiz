<template>
  <div
    :id="'vue-quiz'+$root.postID"
    class="vue-quiz"
  >
    <!-- PYTANIA -->
    <div
      class="questions"
      v-if="questions && !answers"
    >
      <!-- PYTANIE -->
      <div
        class="question"
        v-for="(question, question_key) in questions"
        :key="question_key"
        v-if="currentQuestion === question_key"
      >
        <!-- TEŚĆ PYTANIA -->
        <div class="question-text">{{question.text}}</div>
        <!-- #TEŚĆ PYTANIA -->
        <!-- OBRAZEK -->
        <div
          class="image-wrap"
          v-if="question.image"
        >
          <QuestionImage :media_id="question.image" />
        </div>
        <!-- #OBRAZEK -->
        <!-- ODPOWIEDZI -->
        <div class="answers">
          <!-- ODPOWIEDŹ -->
          <label
            class="answer"
            v-for="answer in question.answers"
            :key="answer.id"
          >
            <input
              type="radio"
              :value="answer.id"
              v-model="userAnswers[question_key]"
            />
            <div class="text">
              {{answer.text}}
            </div>
          </label>
          <!-- #ODPOWIEDŹ -->
        </div>
        <!-- #ODPOWIEDZI -->
        <!-- PRZYCISKI NAWIGACYJNE -->
        <div class="next-btn">
          <MainButton
            :onClick="() => nextQuestion(question_key, userAnswers[question_key] !== '')"
            :disabled="userAnswers[question_key] === ''"
          >
            <span v-if="currentQuestion != (questions.length - 1)">Następne pytanie</span>
            <span v-if="currentQuestion === (questions.length - 1)">Zakończ</span>
          </MainButton>
        </div>
        <!-- #PRZYCISKI NAWIGACYJNE -->
      </div>
      <!-- #PYTANIE -->
    </div>
    <!-- #PYTANIA -->
    <!-- WYNIKI -->
    <div
      class="results"
      v-if="answers"
    >
      <div class="results-text">
        Twój wynik to: <strong>{{points}}</strong> na <strong>{{questions.length}}</strong>
      </div>
      <!-- PRZYCISKI NAWIGACYJNE -->
      <div class="buttons">
        <MainButton
          :onClick="() => showAnswers = true"
          v-if="!showAnswers"
        >Pokaż odpowiedzi</MainButton>
        <MainButton
          :onClick="() => showAnswers = false"
          v-if="showAnswers"
        >Ukryj odpowiedzi</MainButton>
        <MainButton :onClick="() => resetQuiz()">Jeszcze raz !</MainButton>
      </div>
      <!-- #PRZYCISKI NAWIGACYJNE -->
      <!-- PYTANIA -->
      <div
        class="questions"
        v-if="showAnswers"
      >
        <!-- PYTANIE -->
        <div
          class="question"
          v-for="(question, question_key) in questions"
          :key="question_key"
        >
          <!-- TEŚĆ PYTANIA -->
          <div>Pytanie: {{question_key + 1}}</div>
          <div class="question-text">{{question.text}}</div>
          <!-- #TEŚĆ PYTANIA -->
          <!-- OBRAZEK -->
          <div
            class="image-wrap"
            v-if="question.image"
          >
            <QuestionImage :media_id="question.image" />
          </div>
          <!-- #OBRAZEK -->
          <!-- ODPOWIEDZI -->
          <div class="answers">
            <!-- ODPOWIEDŹ -->
            <div
              class="answer"
              v-for="answer in question.answers"
              :key="answer.id"
            >
              <div :class="['text', { 'correct': (answers[question_key] == answer.id) && (userAnswers[question_key] == answer.id) || (answers[question_key] == answer.id) }, { 'wrong': (userAnswers[question_key] == answer.id) && (answers[question_key] != userAnswers[question_key]) }]">
                {{answer.text}}
              </div>
              <!-- #ODPOWIEDŹ -->
            </div>
            <!-- #ODPOWIEDZI -->
          </div>
        </div>
        <!-- #PYTANIE -->
      </div>
      <!-- #PYTANIA -->
    </div>
    <!-- #WYNIKI -->
  </div>
</template>

<script>
//przypisanie jQuery do zmiennej j
const j = jQuery.noConflict();
//import bibliotek
import QuestionImage from "./components/QuestionImage.vue";
import MainButton from "./components/MainButton.vue";

export default {
  name: "App",
  data() {
    return {
      questions: null,
      answers: null,
      userAnswers: [],
      currentQuestion: 0,
      points: 0,
      showAnswers: false
    };
  },
  components: {
    QuestionImage,
    MainButton
  },
  //pobieranie pytań po zainicjowaniu aplikacji
  mounted() {
    const self = this;
    j.ajax({
      type: "POST",
      dataType: "json",
      url: wp.ajax.settings.url,
      data: {
        action: "get_questions",
        post_id: self.$root.postID
      },
      success: function(response) {
        self.questions = response;
        self.questions.forEach((element, index) => {
          self.questions[index].answers = self.shuffle(element.answers);
          self.userAnswers = [...self.userAnswers, ""];
        });
      },
      error: function(error) {
        console.log(error);
      }
    });
  },
  methods: {
    //przechodzenie do następnego pytania
    nextQuestion(id, canNext) {
      if (canNext) {
        if (id < this.questions.length - 1) {
          this.currentQuestion += 1;
        } else {
          //get answers and count points
          const self = this;
          j.ajax({
            type: "POST",
            dataType: "json",
            url: wp.ajax.settings.url,
            data: {
              action: "get_answers",
              post_id: self.$root.postID
            },
            success: function(response) {
              self.answers = response;
              self.answers.forEach((item, id) => {
                if (parseInt(item) === parseInt(self.userAnswers[id])) {
                  self.points += 1;
                }
              });
            },
            error: function(error) {
              console.log(error);
            }
          });
        }
      }
    },
    //resetowanie quizu
    resetQuiz() {
      this.currentQuestion = 0;
      this.answers = null;
      this.points = 0;
      this.userAnswers = [];
      this.questions.forEach((element, index) => {
        this.questions[index].answers = this.shuffle(element.answers);
        this.userAnswers = [...this.userAnswers, ""];
      });
      this.showAnswers = false;
    },
    // funcja mieszająca elementy tablicy
    shuffle(array) {
      let currentIndex = array.length,
        temporaryValue,
        randomIndex;
      while (0 !== currentIndex) {
        randomIndex = Math.floor(Math.random() * currentIndex);
        currentIndex -= 1;
        temporaryValue = array[currentIndex];
        array[currentIndex] = array[randomIndex];
        array[randomIndex] = temporaryValue;
      }
      return array;
    }
  }
};
</script>

<style lang="scss">
@import "../scss/variables.scss";
.vue-quiz {
  .results {
    .results-text {
      font-size: 20px;
      line-height: 26px;
    }
  }
  .buttons {
    display: flex;
    justify-content: space-between;
    margin: 40px 0;
  }
  .questions {
    .question {
      border: 1px solid $color3;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 40px;
      box-shadow: 2px 2px 1px 0px rgba(0, 0, 0, 0.2);
      .question-text {
        font-size: 24px;
        margin-bottom: 20px;
      }
      .next-btn {
        margin-top: 20px;
        text-align: right;
      }
      .answers {
        .answer {
          margin: 0;
          padding: 0;
          input[type="radio"] {
            display: none;
          }
          .text {
            font-weight: normal;
            font-size: 14px;
            line-height: 18px;
            padding: 10px;
            margin-top: 10px;
            border-radius: 10px;
            border: 1px solid $color3;
            background: $color6;
            &.correct {
              background: $green;
            }
            &.wrong {
              background: $red;
            }
          }
          input[type="radio"]:checked + .text {
            color: white;
            background: $color1;
            font-style: normal;
          }
        }
      }
    }
  }
}
</style>
