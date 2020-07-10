pipeline { 
    agent any 
    environment {
        BRANCH_NAME = "${GIT_BRANCH.split("/")[1]}"
    }
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
                    echo BRANCH_NAME
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
