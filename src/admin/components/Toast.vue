<template>
    <transition name="fade">
        <div class="toast">
            {{text}}
        </div>
    </transition>
</template>

<script>
var j = jQuery.noConflict();
export default {
  name: "Toast",
  props: {
    text: String
  }
};
</script>

<style lang="scss">
#vue-quiz {
  .toast {
    position: fixed;
    top: 40px;
    left: 50%;
    right: 0;
    width: 200px;
    margin-left: -100px;
    padding: 20px;
    background: green;
    color: white;
    border-radius: 10px;
    z-index: 100;
    text-align: center;
  }
}
.fade-enter-active, .fade-leave-active {
  transition: all .5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
  transform: translateY(-100%);
}
</style>
