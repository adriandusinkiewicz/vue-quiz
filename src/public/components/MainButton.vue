<template>
  <div
    @click="onClick"
    :class="['main-button', {'disabled': disabled}, {'green': color === 'green'} , {'red': color === 'red'}]"
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
    disabled: Boolean,
    color: String
  }
};
</script>

<style lang="scss">
@import "../../scss/variables.scss";
.vue-quiz {
  .main-button {
    display: inline-flex;
    border-radius: 10px;
    padding: 10px 20px;
    background: $color2;
    color: $white;
    font-size: 14px;
    line-height: 18px;
    font-weight: medium;
    border: 1px solid $color5;
    box-shadow: 2px 2px 1px 0px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    transition: transform 0.3s;
    &.disabled {
      pointer-events: none;
      opacity: 0.4;
    }
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