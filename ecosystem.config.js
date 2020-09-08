module.exports = {
    apps: [
    {
      name: "StargateS0",
      script: "stargateSharded.php",
      args: "0 2",
      max_memory_restart: '4000M', 
      interpreter: 'php',
      cwd: "./",
      watch: false,
      watch_delay: 1000,
      ignore_watch : ["vendor"],

    },
    {
      name: "StargateS1",
      script: "stargateSharded.php",
      args: "1 2",
      max_memory_restart: '4000M', 
      interpreter: 'php',
      cwd: "./",
      watch: false,
      watch_delay: 1000,
      ignore_watch : ["vendor"],

    },


  ]}