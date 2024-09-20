<template>
    <div class="top-menu">
      <div class="menu-items">
        <div class="user-menu">
          <span>{{ clienteLogado.nome }}</span>
          <div class="dropdown">
            <button @click="toggleDropdown">▼</button>
            <div v-if="dropdownOpen" class="dropdown-content">
              <a href="/">Fazer pedido</a>
              <a @click="meusPedidos">Meus pedidos</a>
              <a @click="logout">Logout</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { useForm } from '@inertiajs/vue3';
  export default {
    data() {
      return {
        dropdownOpen: false,
      };
    },
    props: {
      clienteLogado: {
        type: Object,
        required: true,
      },
    },
    methods: {
      toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
      },
      logout() {
        localStorage.removeItem('token');
        window.location.reload();
      },
      meusPedidos() {
        useForm({}).get(route('meus-pedidos'));
      },
    },
  };
  </script>
  
  <style scoped>
  .top-menu {
    color: white;
    display: flex;
    justify-content: flex-end;
    padding: 10px;
    background-color:  rgba(2, 1, 52, 0.878);
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
  }
  
  .user-menu {
    position: relative;
  }
  
  .dropdown {
    display: inline-block;
  }
  
  .dropdown-content {
    display: block;
    position: absolute;
    min-width: 160px;
    cursor: pointer;
    right: 0;
    background-color: rgb(16, 16, 107);
    color: white;
    box-shadow: 0px 8px 16px 0px rgba(6, 6, 146, 0.275);
    z-index: 1;
  }
  
  .dropdown-content a {
    color: white;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
  }
  
  .dropdown-content a:hover {
    background-color: #0f17a5;
  }
  </style>