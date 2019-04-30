<template>
  <div
    @click="onClick"
    :class="['main-button', {'green': color === 'green'} , {'red': color === 'red'}]"
  >
    <slot>Button</slot>
  </div>
</template>

<script>
export default {
  name: "MainButton",
  props: {
    onClick: {
      type: Function,
      required: true
    },
    color: String
  }
};
</script>

<style lang="scss">
@import "../../scss/variables.scss";
#vue-quiz {
  .main-button {
    border-radius: 10px;
    padding: 8px 15px;
    background: $color1;
    color: $white;
    font-size: 12px;
    line-height: 16px;
    font-weight: medium;
    border: 1px solid $color5;
    box-shadow: 2px 2px 1px 0px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    transition: transform 0.3s;
    &.green {
      background: $green;
    }
    &.red {
      background: $red;
    }
    &:active {
      box-shadow: inset 2px 2px 1px 0px rgba(0, 0, 0, 0.2);
    }
    &:hover {
      transform: scale(1.02);
    }
  }
}
</style>