// vite.config.js
import laravel from "file:///E:/Desenvolvimento/js-advogados/app/node_modules/laravel-vite-plugin/dist/index.js";
import { defineConfig } from "file:///E:/Desenvolvimento/js-advogados/app/node_modules/vite/dist/node/index.js";
import { globSync } from "file:///E:/Desenvolvimento/js-advogados/app/node_modules/glob/dist/esm/index.js";
var vite_config_default = defineConfig({
  // base: '/build/',
  plugins: [
    // nodeResolve(),
    laravel({
      input: [
        "resources/css/app.css",
        "resources/sass/app.scss",
        "resources/js/app.js",
        ...globSync("resources/js/views/**/*.js"),
        // Sincroniza todos os arquivos js da pasta resources/js/views
        ...globSync("resources/js/commons/**/*.js")
        // Sincroniza todos os arquivos js da pasta resources/js/views
      ],
      refresh: true
    })
    // commonjs() // Adiciona suporte a módulos CommonJS
  ]
  // build: {
  //     rollupOptions: {
  //         output: {
  //             exports: 'auto', // Isso vai tratar arquivos que não exportam nada explicitamente
  //         },
  //     },
  // },
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJFOlxcXFxEZXNlbnZvbHZpbWVudG9cXFxcanMtYWR2b2dhZG9zXFxcXGFwcFwiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9maWxlbmFtZSA9IFwiRTpcXFxcRGVzZW52b2x2aW1lbnRvXFxcXGpzLWFkdm9nYWRvc1xcXFxhcHBcXFxcdml0ZS5jb25maWcuanNcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfaW1wb3J0X21ldGFfdXJsID0gXCJmaWxlOi8vL0U6L0Rlc2Vudm9sdmltZW50by9qcy1hZHZvZ2Fkb3MvYXBwL3ZpdGUuY29uZmlnLmpzXCI7Ly8gaW1wb3J0IGNvbW1vbmpzIGZyb20gJ0Byb2xsdXAvcGx1Z2luLWNvbW1vbmpzJztcbmltcG9ydCBsYXJhdmVsIGZyb20gJ2xhcmF2ZWwtdml0ZS1wbHVnaW4nO1xuaW1wb3J0IHsgZGVmaW5lQ29uZmlnIH0gZnJvbSAndml0ZSc7XG5cbmltcG9ydCB7IGdsb2JTeW5jIH0gZnJvbSAnZ2xvYic7IC8vIFVzZSBnbG9iU3luYyBjb20gaW1wb3J0IG5hIHZlcnNcdTAwRTNvIEVTTVxuXG4vLyBpbXBvcnQgeyBub2RlUmVzb2x2ZSB9IGZyb20gJ0Byb2xsdXAvcGx1Z2luLW5vZGUtcmVzb2x2ZSc7XG5cbmV4cG9ydCBkZWZhdWx0IGRlZmluZUNvbmZpZyh7XG4gICAgLy8gYmFzZTogJy9idWlsZC8nLFxuICAgIHBsdWdpbnM6IFtcbiAgICAgICAgLy8gbm9kZVJlc29sdmUoKSxcbiAgICAgICAgbGFyYXZlbCh7XG4gICAgICAgICAgICBpbnB1dDogW1xuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvY3NzL2FwcC5jc3MnLFxuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvc2Fzcy9hcHAuc2NzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9hcHAuanMnLFxuICAgICAgICAgICAgICAgIC4uLmdsb2JTeW5jKCdyZXNvdXJjZXMvanMvdmlld3MvKiovKi5qcycpLCAvLyBTaW5jcm9uaXphIHRvZG9zIG9zIGFycXVpdm9zIGpzIGRhIHBhc3RhIHJlc291cmNlcy9qcy92aWV3c1xuICAgICAgICAgICAgICAgIC4uLmdsb2JTeW5jKCdyZXNvdXJjZXMvanMvY29tbW9ucy8qKi8qLmpzJyksIC8vIFNpbmNyb25pemEgdG9kb3Mgb3MgYXJxdWl2b3MganMgZGEgcGFzdGEgcmVzb3VyY2VzL2pzL3ZpZXdzXG4gICAgICAgICAgICBdLFxuICAgICAgICAgICAgcmVmcmVzaDogdHJ1ZSxcbiAgICAgICAgfSksXG4gICAgICAgIC8vIGNvbW1vbmpzKCkgLy8gQWRpY2lvbmEgc3Vwb3J0ZSBhIG1cdTAwRjNkdWxvcyBDb21tb25KU1xuICAgIF0sXG4gICAgLy8gYnVpbGQ6IHtcbiAgICAvLyAgICAgcm9sbHVwT3B0aW9uczoge1xuICAgIC8vICAgICAgICAgb3V0cHV0OiB7XG4gICAgLy8gICAgICAgICAgICAgZXhwb3J0czogJ2F1dG8nLCAvLyBJc3NvIHZhaSB0cmF0YXIgYXJxdWl2b3MgcXVlIG5cdTAwRTNvIGV4cG9ydGFtIG5hZGEgZXhwbGljaXRhbWVudGVcbiAgICAvLyAgICAgICAgIH0sXG4gICAgLy8gICAgIH0sXG4gICAgLy8gfSxcbn0pO1xuIl0sCiAgIm1hcHBpbmdzIjogIjtBQUNBLE9BQU8sYUFBYTtBQUNwQixTQUFTLG9CQUFvQjtBQUU3QixTQUFTLGdCQUFnQjtBQUl6QixJQUFPLHNCQUFRLGFBQWE7QUFBQTtBQUFBLEVBRXhCLFNBQVM7QUFBQTtBQUFBLElBRUwsUUFBUTtBQUFBLE1BQ0osT0FBTztBQUFBLFFBQ0g7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0EsR0FBRyxTQUFTLDRCQUE0QjtBQUFBO0FBQUEsUUFDeEMsR0FBRyxTQUFTLDhCQUE4QjtBQUFBO0FBQUEsTUFDOUM7QUFBQSxNQUNBLFNBQVM7QUFBQSxJQUNiLENBQUM7QUFBQTtBQUFBLEVBRUw7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQVFKLENBQUM7IiwKICAibmFtZXMiOiBbXQp9Cg==
