module.exports = {
    apps: [
    {
      name: "StargateS0",
      script: "stargateSharded.php",
      args: "0 3",
      max_memory_restart: '500M', 
      interpreter: 'php',
      cwd: "./",
      watch: false,
      watch_delay: 1000,
      ignore_watch : ["vendor"],

    },
    {
      name: "StargateS1",
      script: "stargateSharded.php",
      args: "1 3",
      max_memory_restart: '375M', 
      interpreter: 'php',
      cwd: "./",
      watch: false,
      watch_delay: 1000,
      ignore_watch : ["vendor"],
    },
    {
      name: "StargateS2",
      script: "stargateSharded.php",
      args: "2 3",
      max_memory_restart: '350M', 
      interpreter: 'php',
      cwd: "./",
      watch: false,
      watch_delay: 1000,
      ignore_watch : ["vendor"],
    },
 
  ]}