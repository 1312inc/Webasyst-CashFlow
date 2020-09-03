<template>
  <div id="app">
    <h2>It works!</h2>
    <p>Router works too!</p>
    <div id="nav">
      <router-link to="/">Home</router-link> |
      <router-link to="/about">About</router-link>
    </div>
    <router-view/>
  </div>
</template>

<style lang="scss" scoped>
  #app {
    text-align: center;
    padding-top: 4rem;
  }
  h2 {
    margin-bottom: 4rem;
  }
</style>
