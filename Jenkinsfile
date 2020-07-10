pipeline { 
    agent any 
    stages {
        stage('Test'){
            steps {
               echo "TEST"
            }
        }
        stage('Deploy') {
            steps {
                sh 'git status'
                script{
                    echo "BRANCH::: "
                    echo env.BRANCH_NAME
                    switch(env.BRANCH_NAME){
                        case "master":
                            break;
                        case "staging":
                            
                            break;
                        default:
                            echo "skipping";
                            break;
                        
                    }
                }
            }
        }
    }
}
